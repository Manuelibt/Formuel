<?php
/** @var array $values */
/** @var string $message */
/** @var int $timestamp */
?>
<div class="formuel-wrapper">
    <?php if ($message === 'success') : ?>
        <div class="formuel-notice formuel-notice--success">
            <?php echo esc_html__('Thanks! Your message was saved.', 'formuel'); ?>
        </div>
    <?php elseif ($message === 'error') : ?>
        <div class="formuel-notice formuel-notice--error">
            <?php echo esc_html__('Please fill in all fields.', 'formuel'); ?>
        </div>
    <?php endif; ?>

    <form class="formuel-form" method="post">
        <div class="formuel-field formuel-field--honeypot" aria-hidden="true">
            <label for="formuel-hp"><?php echo esc_html__('Leave this field empty', 'formuel'); ?></label>
            <input type="text" id="formuel-hp" name="formuel_hp" value="" autocomplete="off" tabindex="-1" />
        </div>

        <div class="formuel-field">
            <label for="formuel-name"><?php echo esc_html__('Name', 'formuel'); ?></label>
            <input type="text" id="formuel-name" name="formuel_name" value="<?php echo esc_attr($values['name']); ?>" required />
        </div>

        <div class="formuel-field">
            <label for="formuel-email"><?php echo esc_html__('Email', 'formuel'); ?></label>
            <input type="email" id="formuel-email" name="formuel_email" value="<?php echo esc_attr($values['email']); ?>" required />
        </div>

        <div class="formuel-field">
            <label for="formuel-message"><?php echo esc_html__('Message', 'formuel'); ?></label>
            <textarea id="formuel-message" name="formuel_message" rows="5" required><?php echo esc_textarea($values['message']); ?></textarea>
        </div>

        <input type="hidden" name="formuel_time" value="<?php echo esc_attr($timestamp); ?>" />
        <?php wp_nonce_field(Formuel_Shortcode::NONCE_ACTION, 'formuel_nonce'); ?>
        <button class="formuel-button" type="submit" name="formuel_submit" value="1">
            <?php echo esc_html__('Send', 'formuel'); ?>
        </button>
    </form>
</div>
