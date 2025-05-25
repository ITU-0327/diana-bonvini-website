<?php
/**
 * @var \App\View\AppView $this
 */

$this->assign('title', 'Coaching Services');
?>

<div class="max-w-4xl mx-auto p-6">
    <?= $this->element('page_title', ['title' => 'Coaching Services']) ?>

    <div class="space-y-10 text-gray-700">
        <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-2">Interview Skills Coaching</h2>
            <p class="leading-relaxed">
                Our interview skills coaching service provides comprehensive preparation for university admissions interviews, job interviews, and professional assessments. Through personalized one-on-one sessions, we help you develop confidence, improve your communication skills, and master the art of articulating your thoughts clearly and persuasively. Our expert coaches work with you on body language, tone, structure, and content, ensuring you can handle challenging questions with poise. Whether you're preparing for Multiple Mini Interviews (MMI), panel interviews, or traditional one-on-one sessions, we provide tailored strategies and extensive practice to help you succeed. Perfect for students applying to competitive programs or professionals seeking career advancement.
            </p>
        </section>

        <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-2">GAMSAT/UCAT Preparation</h2>
            <p class="leading-relaxed">
                Our specialized GAMSAT and UCAT coaching provides intensive preparation for these crucial medical and dental school admission tests. The Graduate Australian Medical School Admissions Test (GAMSAT) and University Clinical Aptitude Test (UCAT) require specific strategies and extensive practice to achieve competitive scores. Our experienced tutors guide you through all test sections, including verbal reasoning, quantitative reasoning, abstract reasoning, and situational judgment. We provide comprehensive study materials, practice tests under timed conditions, and personalized feedback to identify and strengthen weak areas. Our proven techniques help students improve their test-taking speed, accuracy, and confidence, significantly boosting their chances of securing places at top medical and dental schools.
            </p>
        </section>

        <section>
            <h2 class="text-2xl font-semibold text-gray-900 mb-2">Cambridge Interview Preparation</h2>
            <p class="leading-relaxed">
                Cambridge University interviews are renowned for their academic rigor and unique approach to assessing candidates' intellectual potential. Our Cambridge interview coaching provides specialized preparation for this challenging process, whether you're applying for undergraduate or graduate programs. Our coaches, many of whom are Cambridge alumni, understand the specific expectations and interview styles across different subjects and colleges. We focus on developing your critical thinking skills, problem-solving abilities, and capacity to think on your feet. Through mock interviews, academic discussions, and subject-specific preparation, we help you demonstrate your passion for learning, intellectual curiosity, and ability to engage with complex ideas. Our comprehensive approach ensures you're fully prepared to showcase your potential and secure your place at one of the world's most prestigious universities.
            </p>
        </section>

        <!-- Booking button -->
        <div class="text-center">
            <?= $this->Html->link(
                'Book Coaching Service Now',
                ['controller' => 'CoachingServiceRequests', 'action' => 'add'],
                [
                    'class' => 'inline-block bg-blue-600 text-white px-6 py-3 rounded-lg text-lg font-medium hover:bg-blue-700 transition',
                ],
            ) ?>
        </div>
    </div>
</div> 