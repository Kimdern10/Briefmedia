<?php

namespace Tests\Feature;

use App\Mail\ContactFormMail;
use App\Mail\NewsletterMail;
use App\Mail\OtpMail;
use App\Mail\PasswordResetMail;
use App\Models\Category;
use App\Models\Post;
use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailTemplatesTest extends TestCase
{
    use DatabaseTransactions;

    public function test_verification_email_shows_the_code_in_the_branded_layout(): void
    {
        $mail = new OtpMail(482913);

        $mail->assertHasSubject('Your BriefMedia verification code');
        $mail->assertSeeInHtml('482913');
        $mail->assertSeeInHtml('Confirm it');
        $mail->assertSeeInHtml('Brief<span style="color:#e4407f;">Media</span>', false);
    }

    public function test_password_reset_email_works_with_both_ways_it_is_sent(): void
    {
        $mail = new PasswordResetMail('739201');
        $mail->assertHasSubject('Your BriefMedia password reset code');
        $mail->assertSeeInHtml('739201');

        // ForgotPasswordController renders the same view with $body instead of $code
        $html = view('emails.password_reset', ['body' => '115577'])->render();
        $this->assertStringContainsString('115577', $html);
        $this->assertStringContainsString('Reset your password', $html);
    }

    public function test_contact_email_shows_the_message_and_replies_to_the_visitor(): void
    {
        $mail = new ContactFormMail([
            'name' => 'Ada Reader',
            'email' => 'ada@example.com',
            'subject' => 'Story tip',
            'message' => 'There is a festival in Awka this weekend.',
        ]);

        $mail->assertHasSubject('New message: Story tip');
        $mail->assertHasReplyTo('ada@example.com', 'Ada Reader');
        $mail->assertSeeInHtml('There is a festival in Awka this weekend.');
        $mail->assertSeeInHtml('mailto:ada@example.com', false);
    }

    public function test_newsletter_email_lists_stories_and_an_unsubscribe_link(): void
    {
        [$published, $trending] = $this->makeNewsletterPosts();
        $subscriber = Subscriber::create([
            'email' => 'reader-template-test@example.com',
            'is_active' => true,
            'unsubscribe_token' => 'tok-template-test',
        ]);

        $mail = new NewsletterMail(collect([$published]), collect([$trending]), $subscriber);

        $mail->assertSeeInHtml($published->title);
        $mail->assertSeeInHtml($trending->title);
        $mail->assertSeeInHtml(route('newsletter.unsubscribe', 'tok-template-test'), false);
    }

    public function test_newsletter_command_sends_published_posts_only(): void
    {
        Mail::fake();

        [$published, , $draft] = $this->makeNewsletterPosts();
        Subscriber::create([
            'email' => 'reader-command-test@example.com',
            'is_active' => true,
            'unsubscribe_token' => 'tok-command-test',
        ]);

        $this->artisan('newsletter:send')->assertSuccessful();

        Mail::assertSent(NewsletterMail::class, function (NewsletterMail $mail) use ($published, $draft) {
            return $mail->hasTo('reader-command-test@example.com')
                && $mail->latestPosts->contains('id', $published->id)
                && ! $mail->latestPosts->contains('id', $draft->id);
        });
    }

    /**
     * @return array{0: Post, 1: Post, 2: Post}
     */
    private function makeNewsletterPosts(): array
    {
        $admin = User::factory()->create(['password' => 'password']);
        $category = Category::create(['name' => 'Zz Email Test Category']);

        $make = fn (string $title, string $status, int $views) => Post::create([
            'admin_id' => $admin->id,
            'category_id' => $category->id,
            'title' => $title,
            'excerpt' => 'Excerpt for '.$title,
            'content' => '<p>'.$title.'</p>',
            'featured_image_1' => 'posts/featured/does-not-exist.jpg',
            'status' => $status,
            'views' => $views,
        ]);

        return [
            $make('Zz Newsletter Published Story', 'published', 5),
            $make('Zz Newsletter Trending Story', 'published', 999999),
            $make('Zz Newsletter Draft Story', 'draft', 0),
        ];
    }
}
