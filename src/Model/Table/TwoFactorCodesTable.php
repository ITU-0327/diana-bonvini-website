<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * TwoFactorCodes Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @method \App\Model\Entity\TwoFactorCode newEmptyEntity()
 * @method \App\Model\Entity\TwoFactorCode newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\TwoFactorCode> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\TwoFactorCode get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\TwoFactorCode findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\TwoFactorCode patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\TwoFactorCode> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\TwoFactorCode|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\TwoFactorCode saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\TwoFactorCode>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\TwoFactorCode>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\TwoFactorCode>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\TwoFactorCode> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\TwoFactorCode>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\TwoFactorCode>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\TwoFactorCode>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\TwoFactorCode> deleteManyOrFail(iterable $entities, array $options = [])
 */
class TwoFactorCodesTable extends Table
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

        $this->setTable('two_factor_codes');
        $this->setDisplayField('two_factor_code_id');
        $this->setPrimaryKey('two_factor_code_id');

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
            ->scalar('code')
            ->maxLength('code', 6)
            ->requirePresence('code', 'create')
            ->notEmptyString('code');

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
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);

        return $rules;
    }
}
