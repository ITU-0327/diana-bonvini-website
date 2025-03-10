<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * UserOauths Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 *
 * @method \App\Model\Entity\UserOauth newEmptyEntity()
 * @method \App\Model\Entity\UserOauth newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\UserOauth> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\UserOauth get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\UserOauth findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\UserOauth patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\UserOauth> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\UserOauth|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\UserOauth saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\UserOauth>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\UserOauth>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\UserOauth>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\UserOauth> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\UserOauth>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\UserOauth>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\UserOauth>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\UserOauth> deleteManyOrFail(iterable $entities, array $options = [])
 */
class UserOauthsTable extends Table
{
    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('user_oauths');
        $this->setDisplayField('provider');
        $this->setPrimaryKey('oauth_id');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->uuid('user_id')
            ->notEmptyString('user_id');

        $validator
            ->scalar('provider')
            ->maxLength('provider', 50)
            ->requirePresence('provider', 'create')
            ->notEmptyString('provider');

        $validator
            ->scalar('provider_user_id')
            ->maxLength('provider_user_id', 255)
            ->requirePresence('provider_user_id', 'create')
            ->notEmptyString('provider_user_id');

        $validator
            ->scalar('access_token')
            ->allowEmptyString('access_token');

        $validator
            ->scalar('refresh_token')
            ->allowEmptyString('refresh_token');

        $validator
            ->dateTime('token_expires_at')
            ->allowEmptyDateTime('token_expires_at');

        $validator
            ->notEmptyString('is_deleted');

        $validator
            ->dateTime('created_at')
            ->notEmptyDateTime('created_at');

        $validator
            ->dateTime('updated_at')
            ->notEmptyDateTime('updated_at');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(['provider', 'provider_user_id']), ['errorField' => 'provider']);
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);

        return $rules;
    }
}
