<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\Datasource\EntityInterface;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Users Model
 *
 * @property \App\Model\Table\OrdersTable&\Cake\ORM\Association\HasMany $Orders
 * @property \App\Model\Table\AppointmentsTable&\Cake\ORM\Association\HasMany $Appointments
 * @property \App\Model\Table\WritingServiceRequestsTable&\Cake\ORM\Association\HasMany $WritingServiceRequests
 * @method \App\Model\Entity\User newEmptyEntity()
 * @method \App\Model\Entity\User newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\User> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\User get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\User findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\User patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\User> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\User|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\User saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\User>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\User>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\User>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\User> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\User>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\User>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\User>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\User> deleteManyOrFail(iterable $entities, array $options = [])
 */
class UsersTable extends Table
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

        $this->setTable('users');
        $this->setDisplayField('first_name');
        $this->setPrimaryKey('user_id');

        $this->hasMany('Orders', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('Appointments', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('WritingServiceRequests', [
            'foreignKey' => 'user_id',
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
            ->scalar('first_name')
            ->maxLength('first_name', 255)
            ->requirePresence('first_name', 'create')
            ->notEmptyString('first_name');

        $validator
            ->scalar('last_name')
            ->maxLength('last_name', 255)
            ->requirePresence('last_name', 'create')
            ->notEmptyString('last_name');

        $validator
            ->email('email')
            ->requirePresence('email', 'create')
            ->notEmptyString('email')
            ->add('email', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->scalar('password')
            ->maxLength('password', 255)
            ->requirePresence('password', function ($context) {
                return empty($context['data']['oauth_provider']);
            })
            ->notEmptyString('password', 'Password is required', function ($context) {
                return empty($context['data']['oauth_provider']);
            })
            ->add('password', 'complexity', [
                'rule' => function ($value, $context) {
                    // Skip complexity check if using OAuth.
                    if (!empty($context['data']['oauth_provider'])) {
                        return true;
                    }
                    // Enforce complexity for traditional accounts.
                    return strlen($value) >= 8 &&
                        preg_match('/[A-Z]/', $value) &&
                        preg_match('/[a-z]/', $value) &&
                        preg_match('/\d/', $value);
                },
                'message' => 'Password must be at least 8 characters long and include uppercase, lowercase letters, and a number.',
            ]);

        $validator
            ->scalar('phone_number')
            ->maxLength('phone_number', 50)
            ->allowEmptyString('phone_number');

        $validator
            ->scalar('street_address')
            ->maxLength('street_address', 255)
            ->allowEmptyString('street_address');

        $validator
            ->scalar('street_address2')
            ->maxLength('street_address2', 255)
            ->allowEmptyString('street_address2');

        $validator
            ->scalar('suburb')
            ->maxLength('suburb', 255)
            ->allowEmptyString('suburb');

        $validator
            ->scalar('state')
            ->maxLength('state', 255)
            ->allowEmptyString('state');

        $validator
            ->scalar('postcode')
            ->maxLength('postcode', 20)
            ->allowEmptyString('postcode');

        $validator
            ->scalar('country')
            ->maxLength('country', 2)
            ->allowEmptyString('country');

        $validator
            ->scalar('user_type')
            ->requirePresence('user_type', 'create')
            ->notEmptyString('user_type');

        $validator
            ->scalar('password_reset_token')
            ->maxLength('password_reset_token', 255)
            ->allowEmptyString('password_reset_token');

        $validator
            ->dateTime('token_expiration')
            ->allowEmptyDateTime('token_expiration');

        $validator
            ->dateTime('last_login')
            ->allowEmptyDateTime('last_login');

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
        $rules->add($rules->isUnique(['email']), ['errorField' => 'email']);

        return $rules;
    }

    /**
     * Override delete() to conditionally soft-delete users with dependencies,
     * cascading to orders, appointments, and service requests.
     *
     * @param \Cake\Datasource\EntityInterface $entity The user entity.
     * @param array<string,mixed> $options Options passed from controller.
     * @return bool True on success, false on failure.
     */
    public function delete(EntityInterface $entity, array $options = []): bool
    {
        $userId = $entity->get('user_id');
        $hasDeps =
            $this->Orders->exists(['user_id' => $userId, 'is_deleted' => false]) ||
            $this->Appointments->exists(['user_id' => $userId, 'is_deleted' => false]) ||
            $this->WritingServiceRequests->exists(['user_id' => $userId, 'is_deleted' => false]);

        if ($hasDeps) {
            $rows = $this->updateAll(['is_deleted' => true], ['user_id' => $userId]);
            if ($rows < 1) {
                return false;
            }

            $this->Orders->updateAll(['is_deleted' => true], ['user_id' => $userId]);
            $this->Appointments->updateAll(['is_deleted' => true], ['user_id' => $userId]);
            $this->WritingServiceRequests->updateAll(['is_deleted' => true], ['user_id' => $userId]);

            return true;
        }

        return parent::delete($entity, $options);
    }
}
