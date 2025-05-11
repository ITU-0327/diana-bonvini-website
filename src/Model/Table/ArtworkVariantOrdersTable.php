<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ArtworkVariantOrders Model
 *
 * @property \App\Model\Table\ArtworkVariantsTable&\Cake\ORM\Association\BelongsTo $ArtworkVariants
 * @property \App\Model\Table\OrdersTable&\Cake\ORM\Association\BelongsTo $Orders
 * @method \App\Model\Entity\ArtworkVariantOrder newEmptyEntity()
 * @method \App\Model\Entity\ArtworkVariantOrder newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\ArtworkVariantOrder> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ArtworkVariantOrder get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\ArtworkVariantOrder findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\ArtworkVariantOrder patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\ArtworkVariantOrder> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\ArtworkVariantOrder|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\ArtworkVariantOrder saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\ArtworkVariantOrder>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ArtworkVariantOrder>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ArtworkVariantOrder>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ArtworkVariantOrder> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ArtworkVariantOrder>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ArtworkVariantOrder>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ArtworkVariantOrder>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ArtworkVariantOrder> deleteManyOrFail(iterable $entities, array $options = [])
 */
class ArtworkVariantOrdersTable extends Table
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

        $this->setTable('artwork_variant_orders');
        $this->setDisplayField('artwork_variant_order_id');
        $this->setPrimaryKey('artwork_variant_order_id');

        $this->belongsTo('ArtworkVariants', [
            'foreignKey' => 'artwork_variant_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Orders', [
            'foreignKey' => 'order_id',
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
            ->scalar('order_id')
            ->maxLength('order_id', 9)
            ->notEmptyString('order_id');

        $validator
            ->integer('quantity')
            ->requirePresence('quantity', 'create')
            ->notEmptyString('quantity');

        $validator
            ->decimal('price')
            ->requirePresence('price', 'create')
            ->notEmptyString('price');

        $validator
            ->decimal('subtotal')
            ->requirePresence('subtotal', 'create')
            ->notEmptyString('subtotal');

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
        $rules->add($rules->existsIn(['artwork_variant_id'], 'ArtworkVariants'), ['errorField' => 'artwork_variant_id']);
        $rules->add($rules->existsIn(['order_id'], 'Orders'), ['errorField' => 'order_id']);

        return $rules;
    }
}
