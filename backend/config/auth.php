<?php

return [
    'roles' => [
        'ADMIN' => ['*'],
        'AVUKAT' => ['CASE_VIEW_ALL','CASE_EDIT','DOC_UPLOAD','DOC_DELETE','TASK_MANAGE','WORKFLOW_MANAGE'],
        'STAJYER' => ['CASE_VIEW_OWN','TASK_MANAGE'],
        'SEKRETERYA' => ['CLIENT_MANAGE','SCHEDULE_VIEW','TASK_MANAGE'],
        'FINANS' => ['CASH_VIEW','CASH_EDIT']
    ],
    'permissions' => [
        'CASE_VIEW_ALL',
        'CASE_VIEW_OWN',
        'CASE_EDIT',
        'CLIENT_MANAGE',
        'TASK_MANAGE',
        'WORKFLOW_MANAGE',
        'DOC_UPLOAD',
        'DOC_DELETE',
        'CASH_VIEW',
        'CASH_EDIT',
        'LOG_VIEW',
        'ADMIN_USERS'
    ]
];
