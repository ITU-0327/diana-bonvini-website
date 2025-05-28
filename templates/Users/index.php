<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\User> $users
 */

$this->assign('title', 'Users');
?>
<div class="max-w-6xl mx-auto px-4 py-8">
    <?= $this->element('page_title', ['title' => 'Users']) ?>

    <div class="mb-4 text-right">
        <?= $this->Html->link(
            'New User',
            ['action' => 'add'],
            ['class' => 'bg-teal-600 text-white py-2 px-4 rounded hover:bg-teal-700 transition']
        ) ?>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white rounded-lg shadow">
            <thead>
            <tr class="bg-gray-100 text-gray-600 uppercase text-sm">
                <th class="py-3 px-6 text-left"><?= $this->Paginator->sort('user_id', 'ID') ?></th>
                <th class="py-3 px-6 text-left"><?= $this->Paginator->sort('first_name', 'Name') ?></th>
                <th class="py-3 px-6 text-left"><?= $this->Paginator->sort('email') ?></th>
                <th class="py-3 px-6 text-left"><?= $this->Paginator->sort('user_type', 'Type') ?></th>
                <th class="py-3 px-6 text-left"><?= $this->Paginator->sort('last_login', 'Last Login') ?></th>
                <th class="py-3 px-6 text-center">Actions</th>
            </tr>
            </thead>
            <tbody class="text-gray-700 text-sm">
            <?php foreach ($users as $user): ?>
                <tr class="border-b border-gray-200 hover:bg-gray-50">
                    <td class="py-3 px-6"><?= $this->Format->userId($user->user_id) ?></td>
                    <td class="py-3 px-6"><?= h($user->first_name) ?> <?= h($user->last_name) ?></td>
                    <td class="py-3 px-6"><?= h($user->email) ?></td>
                    <td class="py-3 px-6"><?= h($user->user_type) ?></td>
                    <td class="py-3 px-6"><?= h($user->last_login) ?></td>
                    <td class="py-3 px-6 text-center">
                        <div class="flex justify-center space-x-2">
                            <?= $this->Html->link(
                                'View',
                                ['action' => 'view', $user->user_id],
                                ['class' => 'bg-teal-600 text-white py-1 px-3 rounded hover:bg-teal-700 transition']
                            ) ?>
                            <?= $this->Html->link(
                                'Edit',
                                ['action' => 'edit', $user->user_id],
                                ['class' => 'bg-blue-600 text-white py-1 px-3 rounded hover:bg-blue-700 transition']
                            ) ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        <ul class="flex justify-center space-x-2">
            <?= $this->Paginator->first('<<', ['class' => 'px-3 py-1 bg-gray-200 rounded']) ?>
            <?= $this->Paginator->prev('<', ['class' => 'px-3 py-1 bg-gray-200 rounded']) ?>
            <?= $this->Paginator->numbers(['before' => '', 'after' => '', 'class' => 'px-3 py-1 bg-gray-200 rounded']) ?>
            <?= $this->Paginator->next('>', ['class' => 'px-3 py-1 bg-gray-200 rounded']) ?>
            <?= $this->Paginator->last('>>', ['class' => 'px-3 py-1 bg-gray-200 rounded']) ?>
        </ul>
        <p class="text-center text-gray-600 mt-2">
            <?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?>
        </p>
    </div>
</div>
