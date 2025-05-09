<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * TrustedDevices Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @method \App\Model\Entity\TrustedDevice newEmptyEntity()
 * @method \App\Model\Entity\TrustedDevice newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\TrustedDevice> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\TrustedDevice get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\TrustedDevice findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\TrustedDevice patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\TrustedDevice> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\TrustedDevice|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\TrustedDevice saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\TrustedDevice>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\TrustedDevice>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\TrustedDevice>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\TrustedDevice> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\TrustedDevice>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\TrustedDevice>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\TrustedDevice>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\TrustedDevice> deleteManyOrFail(iterable $entities, array $options = [])
 */
class TrustedDevicesTable extends Table
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

        $this->setTable('trusted_devices');
        $this->setDisplayField('device_id');
        $this->setPrimaryKey('trusted_device_id');

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
            ->scalar('device_id')
            ->maxLength('device_id', 64)
            ->requirePresence('device_id', 'create')
            ->notEmptyString('device_id');

        $validator
            ->dateTime('expires')
            ->requirePresence('expires', 'create')
            ->notEmptyDateTime('expires');

        $validator
            ->dateTime('created_at')
            ->notEmptyDateTime('created_at');

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
        $rules->add($rules->isUnique(['user_id', 'device_id']), ['errorField' => 'user_id']);
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);

        return $rules;
    }
}
