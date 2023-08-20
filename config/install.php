<?php

return [

	'users' => [
		'admin' => [
			'password' => 'admin',
            'groups' => ['root', 'public']
		],
		'guest' => [
			'password' => null,
            'groups' => ['guest', 'public']
		],
	],

	'groups' => [
		'root' => [
            "permissions" => "11111",
            "weight" => 0
		],
		'guest' => [
            "permissions" => "11101",
            "weight" => 10
		],
		'public' => [
            "permissions" => "11001",
            "weight" => 100
		]
	],

    'root' => [
        "groups" => ['root', 'public'],
        "nodes" => [
            "pictures" => [
                "groups" => ["guest"],
                "nodes" => [
                    "photos" => [
                        "groups" => ["guest"],
                        "nodes" => [
                            "grand_canyon" => [
                                "groups" => ["guest"],
                                "extension" => "png",
                            ],
                            "mt.everest" => [
                                "groups" => ["guest"],
                                "extension" => "png",
                            ],
                            "1st_birthday" => [
                                "groups" => ["guest"],
                                "extension" => "png",
                            ]
                        ]
                    ],
                    "scans" => [
                        "groups" => ["guest"],
                        "nodes" => [
                            "2012" => [
                                "groups" => ["guest"],
                                "nodes" => [
                                    "W2" => [
                                        "groups" => ["guest"],
                                        "extension" => "jpg",
                                    ]
                                ]
                            ],
                            "photo_ref_1" => [
                                "groups" => ["guest"],
                                "extension" => "jpg",
                            ],
                            "photo_ref_2" => [
                                "groups" => ["guest"],
                                "extension" => "jpg",
                            ],
                            "wedding_film" => [
                                "groups" => ["guest"],
                                "extension" => "jpg",
                            ]
                        ]
                    ],
                    "landscape" => [
                        "groups" => ["guest"],
                        "extension" => "png",
                    ]
                ]
            ],
            "documents" => [
                "groups" => ["guest"],
                "user_permissions" => [
                    "guest" => "11111"
                ],
                "nodes" => [
                    "personal" => [
                        "groups" => ["guest"],
                        "nodes" => [
                            "assignment_1" => [
                                "groups" => ["guest"],
                                "extension" => "docx",
                            ],
                            "assignment_2" => [
                                "groups" => ["guest"],
                                "extension" => "docx",
                            ]
                        ]
                    ],
                    "business" => [
                        "groups" => ["guest"],
                        "user_permissions" => [
                            "guest" => "11111"
                        ],
                        "nodes" => [
                            "contract_newark" => [
                                "groups" => ["guest"],
                                "extension" => "pdf",
                            ],
                            "contract_omaha" => [
                                "groups" => ["guest"],
                                "extension" => "pdf",
                            ]
                        ]
                    ]
                ]
            ],
            "files" => [
                "groups" => ["public"],
                "nodes" => [
                    "contract_denver" => [
                        "groups" => ["public"],
                        "extension" => "pdf",
                        "user_permissions" => [
                            "admin" => "11001"
                        ],
                    ],
                    "private_assets" => [
                        "groups" => ["root"],
                        "extension" => "pdf"
                    ]
                ]
            ]
        ]
    ]
];
