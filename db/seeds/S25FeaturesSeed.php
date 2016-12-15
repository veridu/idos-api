<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

use Phinx\Seed\AbstractSeed;

class S25FeaturesSeed extends AbstractSeed {
    public function run() {
        $data = [
            [
                'user_id'    => 1,
                'source'     => 'facebook',
                'name'       => 'birthYear',
                'creator'    => 3,
                'type'       => 'integer',
                'value'      => '1985',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => null
            ],
            [
                'user_id'    => 1,
                'source'     => 'facebook',
                'name'       => 'birthMonth',
                'creator'    => 3,
                'type'       => 'integer',
                'value'      => '10',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => null
            ],
            [
                'user_id'    => 1,
                'source'     => 'facebook',
                'name'       => 'birthDay',
                'creator'    => 3,
                'type'       => 'integer',
                'value'      => '13',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => null
            ],
            [
                'user_id'    => 1,
                'source'     => 'facebook',
                'name'       => 'numOfFriends',
                'creator'    => 3,
                'type'       => 'integer',
                'value'      => '4',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => null
            ],
            [
                'user_id'    => 1,
                'source'     => 'facebook',
                'name'       => 'isVerified',
                'creator'    => 3,
                'type'       => 'boolean',
                'value'      => 'false',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => null
            ],
            [
                'user_id'    => 2,
                'source'     => 'linkedin',
                'name'       => 'isCelebrity',
                'creator'    => 3,
                'type'       => 'boolean',
                'value'      => '',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => null
            ],
            [
                'user_id'    => 1,
                'source'     => null,
                'name'       => 'submittedName',
                'creator'    => 1,
                'type'       => 'string',
                'value'      => 'John Doe',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => null
            ],
            [
                'user_id'    => 2,
                'source'     => null,
                'name'       => 'submittedEmail',
                'creator'    => 1,
                'type'       => 'string',
                'value'      => 'johndoe@john.doe',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => null
            ],
        ];

        $features = $this->table('features');
        $features
            ->insert($data)
            ->save();
    }
}
