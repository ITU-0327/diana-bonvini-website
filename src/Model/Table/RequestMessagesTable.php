<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * RequestMessages Model
 *
 * @property \App\Model\Table\WritingServiceRequestsTable&\Cake\ORM\Association\BelongsTo $WritingServiceRequests
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @method \App\Model\Entity\RequestMessage newEmptyEntity()
 * @method \App\Model\Entity\RequestMessage newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\RequestMessage> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\RequestMessage get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\RequestMessage findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\RequestMessage patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\RequestMessage> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\RequestMessage|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\RequestMessage saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\RequestMessage>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\RequestMessage>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\RequestMessage>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\RequestMessage> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\RequestMessage>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\RequestMessage>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\RequestMessage>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\RequestMessage> deleteManyOrFail(iterable $entities, array $options = [])
 */
class RequestMessagesTable extends Table
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

        $this->setTable('request_messages');
        $this->setDisplayField('request_message_id');
        $this->setPrimaryKey('request_message_id');

        $this->belongsTo('WritingServiceRequests', [
            'foreignKey' => 'writing_service_request_id',
            'joinType' => 'INNER',
        ]);
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
            ->scalar('writing_service_request_id')
            ->maxLength('writing_service_request_id', 9)
            ->notEmptyString('writing_service_request_id');

        $validator
            ->uuid('user_id')
            ->notEmptyString('user_id');

        $validator
            ->scalar('message')
            ->requirePresence('message', 'create')
            ->notEmptyString('message');

        $validator
            ->boolean('is_read')
            ->notEmptyString('is_read');

        $validator
            ->boolean('is_deleted')
            ->allowEmptyString('is_deleted');

        $validator
            ->dateTime('created_at')
            ->allowEmptyDateTime('created_at');

        $validator
            ->dateTime('updated_at')
            ->allowEmptyDateTime('updated_at');

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
        $rules->add($rules->existsIn(['writing_service_request_id'], 'WritingServiceRequests'), ['errorField' => 'writing_service_request_id']);
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);

        return $rules;
    }
}
