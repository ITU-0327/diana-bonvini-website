<?php
/**
 * @var \App\View\AppView $this
 */
?>

<!-- Link the CSS file -->
<?= $this->Html->css('about') ?>

<div class="about-page-container">
    <h1>Writing Services</h1>

    <div class="about-content">
        <h2>Creative Writing</h2>
        <p>
            The creative writing service focuses on delivering high-quality, original content tailored to meet a variety of needs. Whether it’s short stories, scriptwriting, brand storytelling, or blog articles, each piece is carefully crafted to align with your vision and engage your audience. By combining imaginative ideas with compelling narratives, this service transforms concepts into captivating written works. Ideal for authors, businesses, and content creators looking to inspire, entertain, or connect with readers on a deeper level.
        </p>

        <h2>Proofreading</h2>
        <p>
            This proofreading service offers a meticulous review of your text to ensure accuracy, clarity, and consistency. Every document undergoes a thorough check for grammar, spelling, punctuation, and formatting errors, ensuring that your writing meets the highest standards of professionalism. Suitable for academic papers, business documents, publications, and personal writing, this service helps to present polished and error-free content that enhances credibility and readability.
        </p>

        <h2>Editing Services</h2>
        <p>
            The editing service provides in-depth refinement of your writing beyond basic proofreading. It focuses on improving the structure, flow, clarity, and tone of your content, ensuring that your message is communicated effectively and persuasively. Whether it’s a manuscript, academic paper, business proposal, or web content, this service helps enhance readability, strengthen arguments, and tailor the writing style to fit the target audience. The goal is to elevate the overall quality of the text, making it impactful and engaging.
        </p>

        <!-- Add the booking button -->
        <div class="booking-button-container">
            <?= $this->Html->link(
                'Book Writing Service Now',
                ['controller' => 'WritingServiceRequests', 'action' => 'add'],
                ['class' => 'booking-button']
            ) ?>
        </div>
    </div>
</div>
