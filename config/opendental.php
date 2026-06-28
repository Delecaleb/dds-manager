<?php

return [

    'url'=>env(
        'OPENDENTAL_URL',
        'https://api.opendental.com/api/v1'
    ),


    'developer_key'=>env(
        'OPENDENTAL_DEVELOPER_KEY'
    ),


    'customer_key'=>env(
        'OPENDENTAL_CUSTOMER_KEY'
    )

];