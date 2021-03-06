<?php
/*
 * Copyright (c) 2012-2017 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

use Phinx\Seed\AbstractSeed;

class S40SubscriptionsSeed extends AbstractSeed {
    public function run() {
        $now = date('Y-m-d H:i:s');

        $data = [
            [
                'category_name' => 'firstNameMismatch',
                'credential_id' => 1,
                'identity_id'   => 1,
                'created_at'    => $now
            ],
            [
                'category_name' => 'lastNameMismatch',
                'credential_id' => 1,
                'identity_id'   => 1,
                'created_at'    => $now
            ]
        ];

        $subscriptions = $this->table('subscriptions');
        $subscriptions
            ->insert($data)
            ->save();
    }
}
