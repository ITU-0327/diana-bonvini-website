<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\WritingServiceRequest;
use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * WritingServiceRequests Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @property \App\Model\Table\RequestMessagesTable&\Cake\ORM\Association\HasMany $RequestMessages
 * @method \App\Model\Entity\WritingServiceRequest newEmptyEntity()
 * @method \App\Model\Entity\WritingServiceRequest newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\WritingServiceRequest> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\WritingServiceRequest get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\WritingServiceRequest findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\WritingServiceRequest patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\WritingServiceRequest> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\WritingServiceRequest|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\WritingServiceRequest saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\WritingServiceRequest>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\WritingServiceRequest>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\WritingServiceRequest>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\WritingServiceRequest> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\WritingServiceRequest>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\WritingServiceRequest>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\WritingServiceRequest>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\WritingServiceRequest> deleteManyOrFail(iterable $entities, array $options = [])
 */
class WritingServiceRequestsTable extends Table
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

        $this->setTable('writing_service_requests');
        $this->setDisplayField('service_type');
        $this->setPrimaryKey('writing_service_request_id');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);

        $this->hasMany('RequestMessages', [
            'foreignKey' => 'writing_service_request_id',
            'sort' => ['RequestMessages.created_at' => 'DESC'],
        ]);
    }

    /**
     * Before save event callback.
     *
     * @param \Cake\Event\EventInterface<\App\Model\Entity\WritingServiceRequest> $event The event object.
     * @param \App\Model\Entity\WritingServiceRequest $entity The entity being saved.
     * @param \ArrayObject<string, mixed> $options Options passed to the save method.
     * @return void
     * @throws \Random\RandomException
     */
    public function beforeSave(EventInterface $event, WritingServiceRequest $entity, ArrayObject $options): void
    {
        if ($entity->isNew() && empty($entity->writing_service_request_id)) {
            $entity->writing_service_request_id = $this->generateRequestId();
        }
    }

    /**
     * Generates a unique request ID.
     *
     * @return string
     * @throws \Random\RandomException
     */
    private function generateRequestId(): string
    {
        do {
            $letters = '';
            for ($i = 0; $i < 2; $i++) {
                $letters .= chr(random_int(65, 90));
            }
            $digits = str_pad((string)random_int(0, 99999), 5, '0', STR_PAD_LEFT);
            $requestId = 'R-' . $letters . $digits;
        } while ($this->exists(['writing_service_request_id' => $requestId]));

        return $requestId;
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
            ->scalar('service_title')
            ->maxLength('service_title', 100, 'Title must be no more than 100 characters.')
            ->requirePresence('service_title', 'create')
            ->notEmptyString('service_title');

        $validator
            ->scalar('service_type')
            ->requirePresence('service_type', 'create')
            ->notEmptyString('service_type');

        $validator
            ->scalar('notes')
            ->maxLength('notes', 1000, 'Notes must be no more than 1000 characters.')
            ->allowEmptyString('notes');

        $validator
            ->decimal('final_price')
            ->allowEmptyString('final_price');

        $validator
            ->scalar('request_status')
            ->notEmptyString('request_status');

        $validator
            ->scalar('document')
            ->maxLength('document', 255)
            ->allowEmptyString('document');

        $validator
            ->boolean('is_deleted')
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
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);

        return $rules;
    }

    /**
     * Override delete() to conditionally soft-delete service requests,
     * cascading to request_messages when needed.
     *
     * @param \Cake\Datasource\EntityInterface $entity The service request entity.
     * @param array<string,mixed> $options Options passed from controller.
     * @return bool True on success, false on failure.
     */
    public function delete(EntityInterface $entity, array $options = []): bool
    {
        $reqId = $entity->get('writing_service_request_id');
        $hasMsgs = $this->RequestMessages->exists(['writing_service_request_id' => $reqId]);

        if ($hasMsgs) {
            $rows = $this->updateAll(['is_deleted' => true], ['writing_service_request_id' => $reqId]);
            if ($rows < 1) {
                return false;
            }

            $this->RequestMessages->updateAll(['is_deleted' => true], ['writing_service_request_id' => $reqId]);

            return true;
        }

        return parent::delete($entity, $options);
    }
}
