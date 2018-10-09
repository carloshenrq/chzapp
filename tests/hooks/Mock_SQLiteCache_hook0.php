[
    'properties' => [
        'key1' => 'value1'
    ],

    'events' => [
        'testEvent' => function() {
            $this->key1 = 'value2';

            if($this->key1 != 'value2')
                return;

            $this->performInstall();
        }
    ],

    'init' => function() {

    },

    'methods' => [
        'performInstall' => function()
        {
        }
    ]
];
