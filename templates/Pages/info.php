<?php
/**
 * @var \App\View\AppView $this
 */
?>

<div class="max-w-4xl mx-auto p-6">
    <?= $this->element('page_title', ['title' => 'Writing Services']) ?>

    <div class="space-y-10 text-gray-700">
        <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-2">Creative Writing</h2>
            <p class="leading-relaxed">
                The creative writing service focuses on delivering high-quality, original content tailored to meet a variety of needs. Whether it’s short stories, scriptwriting, brand storytelling, or blog articles, each piece is carefully crafted to align with your vision and engage your audience. By combining imaginative ideas with compelling narratives, this service transforms concepts into captivating written works. Ideal for authors, businesses, and content creators looking to inspire, entertain, or connect with readers on a deeper level.
            </p>
        </section>

        <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-2">Proofreading</h2>
            <p class="leading-relaxed">
                This proofreading service offers a meticulous review of your text to ensure accuracy, clarity, and consistency. Every document undergoes a thorough check for grammar, spelling, punctuation, and formatting errors, ensuring that your writing meets the highest standards of professionalism. Suitable for academic papers, business documents, publications, and personal writing, this service helps to present polished and error-free content that enhances credibility and readability.
            </p>
        </section>

        <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-2">Editing Services</h2>
            <p class="leading-relaxed">
                The editing service provides in-depth refinement of your writing beyond basic proofreading. It focuses on improving the structure, flow, clarity, and tone of your content, ensuring that your message is communicated effectively and persuasively. Whether it’s a manuscript, academic paper, business proposal, or web content, this service helps enhance readability, strengthen arguments, and tailor the writing style to fit the target audience. The goal is to elevate the overall quality of the text, making it impactful and engaging.
            </p>
        </section>

        <!-- Booking button -->
        <div class="text-center">
            <?= $this->Html->link(
                'Book Writing Service Now',
                ['controller' => 'WritingServiceRequests', 'action' => 'add'],
                [
                    'class' => 'inline-block bg-blue-600 text-white px-6 py-3 rounded-lg text-lg font-medium hover:bg-blue-700 transition',
                ],
            ) ?>
        </div>
    </div>
</div>
