<?php

return [

    'approval_positions' => explode(',', env(
        'APPROVAL_POSITIONS',
        'Manager,Supervisor'
    )),

     'bypass_user_ids' => explode(',', env(
        'BYPASS_USER_IDS',
        ''
    )),

];