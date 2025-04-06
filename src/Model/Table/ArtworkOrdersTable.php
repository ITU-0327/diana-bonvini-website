<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ArtworkOrders Model
 *
 * @property \App\Model\Table\OrdersTable&\Cake\ORM\Association\BelongsTo $Orders
 * @property \App\Model\Table\ArtworksTable&\Cake\ORM\Association\BelongsTo $Artworks
 * @method \App\Model\Entity\ArtworkOrder newEmptyEntity()
 * @method \App\Model\Entity\ArtworkOrder newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\ArtworkOrder> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ArtworkOrder get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\ArtworkOrder findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\ArtworkOrder patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\ArtworkOrder> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\ArtworkOrder|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\ArtworkOrder saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\ArtworkOrder>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ArtworkOrder>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ArtworkOrder>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ArtworkOrder> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ArtworkOrder>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ArtworkOrder>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ArtworkOrder>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ArtworkOrder> deleteManyOrFail(iterable $entities, array $options = [])
 */
class ArtworkOrdersTable extends Table
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

        $this->setTable('artwork_orders');
        $this->setDisplayField('artwork_order_id');
        $this->setPrimaryKey('artwork_order_id');

        $this->belongsTo('Orders', [
            'foreignKey' => 'order_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Artworks', [
            'foreignKey' => 'artwork_id',
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
        // Remove the requirePresence and notEmptyString for order_id so it can be filled later.
        $validator
            ->scalar('order_id')
            ->maxLength('order_id', 20);

        // Accept artwork_id in the format "A001"
        $validator
            ->scalar('artwork_id')
            ->maxLength('artwork_id', 20)
            ->requirePresence('artwork_id', 'create')
            ->notEmptyString('artwork_id');

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
        $rules->add($rules->existsIn(['order_id'], 'Orders'), ['errorField' => 'order_id']);
        $rules->add($rules->existsIn(['artwork_id'], 'Artworks'), ['errorField' => 'artwork_id']);

        return $rules;
    }
}
