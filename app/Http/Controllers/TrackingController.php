<?php

namespace App\Http\Controllers;

use App\Domain\Marketing\TrackingIngestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * The public half of web tracking: the script tracked sites load, and the endpoint it
 * beacons to. Both are unauthenticated by necessity — they are called by visitors'
 * browsers on other people's domains.
 *
 * Everything either route accepts is untrusted. The site key decides which site an event
 * belongs to and nothing else; TrackingIngestService validates and truncates the rest.
 */
class TrackingController extends Controller
{
    /** Largest beacon accepted, in bytes. A page view needs well under 1 KB. */
    private const MAX_BODY_BYTES = 8192;

    /**
     * The tracker itself, served from the app so a site never has to update its snippet.
     *
     * Kept deliberately small and dependency-free: it runs on other people's sites, so it
     * must not slow a page down or collide with anything already loaded there.
     */
    public function script(): Response
    {
        $endpoint = route('tracking.collect');

        $js = <<<JS
        (function () {
          var s = document.currentScript;
          var key = s && s.dataset ? s.dataset.key : null;
          if (!key) return;

          var ENDPOINT = '{$endpoint}';
          var VKEY = 'dds_v_' + key, SKEY = 'dds_s_' + key;

          function get(store, k) { try { return store.getItem(k); } catch (e) { return null; } }
          function set(store, k, v) { try { store.setItem(k, v); } catch (e) {} }

          function send(type, data) {
            var body = JSON.stringify({
              k: key,
              v: get(localStorage, VKEY),
              s: get(sessionStorage, SKEY),
              t: type,
              url: location.href,
              ref: document.referrer || '',
              title: document.title || '',
              d: data || null
            });

            // keepalive lets the beacon survive the page being closed mid-flight.
            fetch(ENDPOINT, {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: body,
              keepalive: true,
              credentials: 'omit',
              mode: 'cors'
            }).then(function (r) { return r.ok ? r.json() : null; })
              .then(function (j) {
                if (!j) return;
                if (j.v) set(localStorage, VKEY, j.v);
                if (j.s) set(sessionStorage, SKEY, j.s);
              })
              .catch(function () { /* tracking must never break the host page */ });
          }

          // Queue anything the page called before this script finished loading.
          var queued = window.ddsq || [];
          window.dds = function (type, data) { send(type, data); };
          for (var i = 0; i < queued.length; i++) window.dds.apply(null, queued[i]);
          window.ddsq = { push: function (args) { window.dds.apply(null, args); } };

          send('pageview');

          // Single-page sites change the URL without reloading; report those as page views too.
          var lastPath = location.href;
          function onRouteChange() {
            if (location.href === lastPath) return;
            lastPath = location.href;
            send('pageview');
          }
          ['pushState', 'replaceState'].forEach(function (m) {
            var original = history[m];
            history[m] = function () { var r = original.apply(this, arguments); onRouteChange(); return r; };
          });
          window.addEventListener('popstate', onRouteChange);
        })();
        JS;

        return response($js, 200, [
            'Content-Type' => 'application/javascript; charset=utf-8',
            'Cache-Control' => 'public, max-age=3600',
            'Access-Control-Allow-Origin' => '*',
        ]);
    }

    /**
     * Receive one event. Answers with the ids the browser should keep, so a visitor stays
     * the same person across visits and the journey can be assembled later.
     */
    public function collect(Request $request, TrackingIngestService $ingest): JsonResponse
    {
        if (strlen((string) $request->getContent()) > self::MAX_BODY_BYTES) {
            return $this->cors(response()->json(['ok' => false], 413));
        }

        $payload = $request->json()->all();

        if (! is_array($payload)) {
            return $this->cors(response()->json(['ok' => false], 422));
        }

        $ids = $ingest->record($payload, $request->userAgent());

        // An unknown or disabled key gets the same shape of answer as a good one: the
        // endpoint is public, so it should not help anyone probe for valid keys.
        if ($ids === null) {
            return $this->cors(response()->json(['ok' => true]));
        }

        return $this->cors(response()->json([
            'ok' => true,
            'v' => $ids['visitor_uid'],
            's' => $ids['session_uid'],
        ]));
    }

    /** Browsers preflight the beacon because it sends JSON. */
    public function options(): Response
    {
        return $this->cors(response('', 204));
    }

    /**
     * Tracked sites are third-party domains, so the beacon is open by design. It carries no
     * cookies (credentials: 'omit'), so an open origin cannot be used to act as a user.
     *
     * @template T of \Symfony\Component\HttpFoundation\Response
     *
     * @param  T  $response
     * @return T
     */
    private function cors($response)
    {
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'POST, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type');
        $response->headers->set('Access-Control-Max-Age', '86400');

        return $response;
    }
}
