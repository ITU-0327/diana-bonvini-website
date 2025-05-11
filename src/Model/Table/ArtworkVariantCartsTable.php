<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ArtworkVariantCarts Model
 *
 * @property \App\Model\Table\ArtworkVariantsTable&\Cake\ORM\Association\BelongsTo $ArtworkVariants
 * @property \App\Model\Table\CartsTable&\Cake\ORM\Association\BelongsTo $Carts
 * @method \App\Model\Entity\ArtworkVariantCart newEmptyEntity()
 * @method \App\Model\Entity\ArtworkVariantCart newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\ArtworkVariantCart> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ArtworkVariantCart get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\ArtworkVariantCart findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\ArtworkVariantCart patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\ArtworkVariantCart> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\ArtworkVariantCart|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\ArtworkVariantCart saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\ArtworkVariantCart>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ArtworkVariantCart>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ArtworkVariantCart>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ArtworkVariantCart> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ArtworkVariantCart>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ArtworkVariantCart>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ArtworkVariantCart>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ArtworkVariantCart> deleteManyOrFail(iterable $entities, array $options = [])
 */
class ArtworkVariantCartsTable extends Table
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

        $this->setTable('artwork_variant_carts');
        $this->setDisplayField('artwork_variant_cart_id');
        $this->setPrimaryKey('artwork_variant_cart_id');

        $this->belongsTo('ArtworkVariants', [
            'foreignKey' => 'artwork_variant_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Carts', [
            'foreignKey' => 'cart_id',
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
            ->uuid('artwork_variant_id')
            ->notEmptyString('artwork_variant_id');

        $validator
            ->uuid('cart_id')
            ->notEmptyString('cart_id');

        $validator
            ->integer('quantity')
            ->notEmptyString('quantity');

        $validator
            ->dateTime('date_added')
            ->notEmptyDateTime('date_added');

        $validator
            ->boolean('is_deleted')
            ->notEmptyString('is_deleted');

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
        $rules->add($rules->isUnique(['cart_id', 'artwork_variant_id']), ['errorField' => 'cart_id']);
        $rules->add($rules->existsIn(['artwork_variant_id'], 'ArtworkVariants'), ['errorField' => 'artwork_variant_id']);
        $rules->add($rules->existsIn(['cart_id'], 'Carts'), ['errorField' => 'cart_id']);

        return $rules;
    }
}
