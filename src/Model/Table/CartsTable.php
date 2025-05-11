<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Carts Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @property \App\Model\Table\ArtworkVariantCartsTable&\Cake\ORM\Association\HasMany $ArtworkVariantCarts
 * @method \App\Model\Entity\Cart newEmptyEntity()
 * @method \App\Model\Entity\Cart newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Cart> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Cart get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Cart findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Cart patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Cart> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Cart|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Cart saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Cart>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Cart>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Cart>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Cart> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Cart>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Cart>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Cart>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Cart> deleteManyOrFail(iterable $entities, array $options = [])
 */
class CartsTable extends Table
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

        $this->setTable('carts');
        $this->setDisplayField('cart_id');
        $this->setPrimaryKey('cart_id');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('ArtworkVariantCarts', [
            'foreignKey' => 'cart_id',
            'saveStrategy' => 'replace',
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
            ->allowEmptyString('user_id')
            ->add('user_id', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->scalar('session_id')
            ->maxLength('session_id', 255)
            ->allowEmptyString('session_id')
            ->add('session_id', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

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
        $rules->add($rules->isUnique(['user_id'], ['allowMultipleNulls' => true]), ['errorField' => 'user_id']);
        $rules->add($rules->isUnique(['session_id'], ['allowMultipleNulls' => true]), ['errorField' => 'session_id']);
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);

        return $rules;
    }
}
