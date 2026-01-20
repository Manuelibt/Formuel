<?php
/** @var array $values */
/** @var string $message */
/** @var array $errors */
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
        <div class="formuel-field">
            <label for="formuel-name"><?php echo esc_html__('Name', 'formuel'); ?></label>
            <input type="text" id="formuel-name" name="formuel_name" value="<?php echo esc_attr($values['name']); ?>" required />
            <?php if (!empty($errors['name'])) : ?>
                <p class="formuel-field__error"><?php echo esc_html($errors['name']); ?></p>
            <?php endif; ?>
        </div>

        <div class="formuel-field">
            <label for="formuel-email"><?php echo esc_html__('Email', 'formuel'); ?></label>
            <input type="email" id="formuel-email" name="formuel_email" value="<?php echo esc_attr($values['email']); ?>" required />
            <?php if (!empty($errors['email'])) : ?>
                <p class="formuel-field__error"><?php echo esc_html($errors['email']); ?></p>
            <?php endif; ?>
        </div>

        <div class="formuel-field">
            <label for="formuel-message"><?php echo esc_html__('Message', 'formuel'); ?></label>
            <textarea id="formuel-message" name="formuel_message" rows="5" required><?php echo esc_textarea($values['message']); ?></textarea>
            <?php if (!empty($errors['message'])) : ?>
                <p class="formuel-field__error"><?php echo esc_html($errors['message']); ?></p>
            <?php endif; ?>
        </div>

        <?php wp_nonce_field(Formuel_Shortcode::NONCE_ACTION, 'formuel_nonce'); ?>
        <button class="formuel-button" type="submit" name="formuel_submit" value="1">
            <?php echo esc_html__('Send', 'formuel'); ?>
        </button>
    </form>
</div>
