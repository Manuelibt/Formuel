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
        <div class="formuel-field formuel-field--honeypot" aria-hidden="true">
            <label for="formuel-hp"><?php echo esc_html__('Leave this field empty', 'formuel'); ?></label>
            <input type="text" id="formuel-hp" name="formuel_hp" value="" autocomplete="off" tabindex="-1" />
        </div>

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
            <label for="formuel-subject"><?php echo esc_html__('Subject', 'formuel'); ?></label>
            <input type="text" id="formuel-subject" name="formuel_subject" value="<?php echo esc_attr($values['subject']); ?>" required />
        </div>

        <div class="formuel-field">
            <label for="formuel-inquiry-type"><?php echo esc_html__('Inquiry type', 'formuel'); ?></label>
            <select id="formuel-inquiry-type" name="formuel_inquiry_type" class="formuel-select">
                <option value="general" <?php selected($values['inquiry_type'], 'general'); ?>><?php echo esc_html__('General', 'formuel'); ?></option>
                <option value="support" <?php selected($values['inquiry_type'], 'support'); ?>><?php echo esc_html__('Support', 'formuel'); ?></option>
                <option value="other" <?php selected($values['inquiry_type'], 'other'); ?>><?php echo esc_html__('Other', 'formuel'); ?></option>
            </select>
        </div>

        <div class="formuel-field formuel-field--conditional <?php echo $values['inquiry_type'] === 'other' ? '' : 'formuel-field--hidden'; ?>" data-condition="other">
            <label for="formuel-other-details"><?php echo esc_html__('Please describe your request', 'formuel'); ?></label>
            <textarea id="formuel-other-details" name="formuel_other_details" rows="3"><?php echo esc_textarea($values['other_details']); ?></textarea>
        </div>

        <div class="formuel-field">
            <fieldset class="formuel-checkbox-group">
                <legend><?php echo esc_html__('Preferences', 'formuel'); ?></legend>
                <label>
                    <input type="checkbox" name="formuel_newsletter" value="1" <?php checked($values['newsletter_opt_in'], 'yes'); ?> />
                    <?php echo esc_html__('I want to receive updates.', 'formuel'); ?>
                </label>
            </fieldset>
        </div>

        <div class="formuel-field">
            <label for="formuel-attachment"><?php echo esc_html__('Attachment', 'formuel'); ?></label>
            <input type="file" id="formuel-attachment" name="formuel_attachment" />
        </div>

        <div class="formuel-field">
            <label for="formuel-subject"><?php echo esc_html__('Subject', 'formuel'); ?></label>
            <input type="text" id="formuel-subject" name="formuel_subject" value="<?php echo esc_attr($values['subject']); ?>" required />
        </div>

        <div class="formuel-field">
            <label for="formuel-inquiry-type"><?php echo esc_html__('Inquiry type', 'formuel'); ?></label>
            <select id="formuel-inquiry-type" name="formuel_inquiry_type" class="formuel-select">
                <option value="general" <?php selected($values['inquiry_type'], 'general'); ?>><?php echo esc_html__('General', 'formuel'); ?></option>
                <option value="support" <?php selected($values['inquiry_type'], 'support'); ?>><?php echo esc_html__('Support', 'formuel'); ?></option>
                <option value="other" <?php selected($values['inquiry_type'], 'other'); ?>><?php echo esc_html__('Other', 'formuel'); ?></option>
            </select>
        </div>

        <div class="formuel-field formuel-field--conditional <?php echo $values['inquiry_type'] === 'other' ? '' : 'formuel-field--hidden'; ?>" data-condition="other">
            <label for="formuel-other-details"><?php echo esc_html__('Please describe your request', 'formuel'); ?></label>
            <textarea id="formuel-other-details" name="formuel_other_details" rows="3"><?php echo esc_textarea($values['other_details']); ?></textarea>
        </div>

        <div class="formuel-field">
            <fieldset class="formuel-checkbox-group">
                <legend><?php echo esc_html__('Preferences', 'formuel'); ?></legend>
                <label>
                    <input type="checkbox" name="formuel_newsletter" value="1" <?php checked($values['newsletter_opt_in'], 'yes'); ?> />
                    <?php echo esc_html__('I want to receive updates.', 'formuel'); ?>
                </label>
            </fieldset>
        </div>

        <div class="formuel-field">
            <label for="formuel-attachment"><?php echo esc_html__('Attachment', 'formuel'); ?></label>
            <input type="file" id="formuel-attachment" name="formuel_attachment" />
        </div>

        <div class="formuel-field">
            <label for="formuel-subject"><?php echo esc_html__('Subject', 'formuel'); ?></label>
            <input type="text" id="formuel-subject" name="formuel_subject" value="<?php echo esc_attr($values['subject']); ?>" required />
        </div>

        <div class="formuel-field">
            <label for="formuel-inquiry-type"><?php echo esc_html__('Inquiry type', 'formuel'); ?></label>
            <select id="formuel-inquiry-type" name="formuel_inquiry_type" class="formuel-select">
                <option value="general" <?php selected($values['inquiry_type'], 'general'); ?>><?php echo esc_html__('General', 'formuel'); ?></option>
                <option value="support" <?php selected($values['inquiry_type'], 'support'); ?>><?php echo esc_html__('Support', 'formuel'); ?></option>
                <option value="other" <?php selected($values['inquiry_type'], 'other'); ?>><?php echo esc_html__('Other', 'formuel'); ?></option>
            </select>
        </div>

        <div class="formuel-field formuel-field--conditional <?php echo $values['inquiry_type'] === 'other' ? '' : 'formuel-field--hidden'; ?>" data-condition="other">
            <label for="formuel-other-details"><?php echo esc_html__('Please describe your request', 'formuel'); ?></label>
            <textarea id="formuel-other-details" name="formuel_other_details" rows="3"><?php echo esc_textarea($values['other_details']); ?></textarea>
        </div>

        <div class="formuel-field">
            <fieldset class="formuel-checkbox-group">
                <legend><?php echo esc_html__('Preferences', 'formuel'); ?></legend>
                <label>
                    <input type="checkbox" name="formuel_newsletter" value="1" <?php checked($values['newsletter_opt_in'], 'yes'); ?> />
                    <?php echo esc_html__('I want to receive updates.', 'formuel'); ?>
                </label>
            </fieldset>
        </div>

        <div class="formuel-field">
            <label for="formuel-attachment"><?php echo esc_html__('Attachment', 'formuel'); ?></label>
            <input type="file" id="formuel-attachment" name="formuel_attachment" />
        </div>

        <div class="formuel-field">
            <label for="formuel-subject"><?php echo esc_html__('Subject', 'formuel'); ?></label>
            <input type="text" id="formuel-subject" name="formuel_subject" value="<?php echo esc_attr($values['subject']); ?>" required />
        </div>

        <div class="formuel-field">
            <label for="formuel-inquiry-type"><?php echo esc_html__('Inquiry type', 'formuel'); ?></label>
            <select id="formuel-inquiry-type" name="formuel_inquiry_type" class="formuel-select">
                <option value="general" <?php selected($values['inquiry_type'], 'general'); ?>><?php echo esc_html__('General', 'formuel'); ?></option>
                <option value="support" <?php selected($values['inquiry_type'], 'support'); ?>><?php echo esc_html__('Support', 'formuel'); ?></option>
                <option value="other" <?php selected($values['inquiry_type'], 'other'); ?>><?php echo esc_html__('Other', 'formuel'); ?></option>
            </select>
        </div>

        <div class="formuel-field formuel-field--conditional <?php echo $values['inquiry_type'] === 'other' ? '' : 'formuel-field--hidden'; ?>" data-condition="other">
            <label for="formuel-other-details"><?php echo esc_html__('Please describe your request', 'formuel'); ?></label>
            <textarea id="formuel-other-details" name="formuel_other_details" rows="3"><?php echo esc_textarea($values['other_details']); ?></textarea>
        </div>

        <div class="formuel-field">
            <fieldset class="formuel-checkbox-group">
                <legend><?php echo esc_html__('Preferences', 'formuel'); ?></legend>
                <label>
                    <input type="checkbox" name="formuel_newsletter" value="1" <?php checked($values['newsletter_opt_in'], 'yes'); ?> />
                    <?php echo esc_html__('I want to receive updates.', 'formuel'); ?>
                </label>
            </fieldset>
        </div>

        <div class="formuel-field">
            <label for="formuel-attachment"><?php echo esc_html__('Attachment', 'formuel'); ?></label>
            <input type="file" id="formuel-attachment" name="formuel_attachment" />
        </div>

        <div class="formuel-field">
            <label for="formuel-subject"><?php echo esc_html__('Subject', 'formuel'); ?></label>
            <input type="text" id="formuel-subject" name="formuel_subject" value="<?php echo esc_attr($values['subject']); ?>" required />
        </div>

        <div class="formuel-field">
            <label for="formuel-inquiry-type"><?php echo esc_html__('Inquiry type', 'formuel'); ?></label>
            <select id="formuel-inquiry-type" name="formuel_inquiry_type" class="formuel-select">
                <option value="general" <?php selected($values['inquiry_type'], 'general'); ?>><?php echo esc_html__('General', 'formuel'); ?></option>
                <option value="support" <?php selected($values['inquiry_type'], 'support'); ?>><?php echo esc_html__('Support', 'formuel'); ?></option>
                <option value="other" <?php selected($values['inquiry_type'], 'other'); ?>><?php echo esc_html__('Other', 'formuel'); ?></option>
            </select>
        </div>

        <div class="formuel-field formuel-field--conditional <?php echo $values['inquiry_type'] === 'other' ? '' : 'formuel-field--hidden'; ?>" data-condition="other">
            <label for="formuel-other-details"><?php echo esc_html__('Please describe your request', 'formuel'); ?></label>
            <textarea id="formuel-other-details" name="formuel_other_details" rows="3"><?php echo esc_textarea($values['other_details']); ?></textarea>
        </div>

        <div class="formuel-field">
            <fieldset class="formuel-checkbox-group">
                <legend><?php echo esc_html__('Preferences', 'formuel'); ?></legend>
                <label>
                    <input type="checkbox" name="formuel_newsletter" value="1" <?php checked($values['newsletter_opt_in'], 'yes'); ?> />
                    <?php echo esc_html__('I want to receive updates.', 'formuel'); ?>
                </label>
            </fieldset>
        </div>

        <div class="formuel-field">
            <label for="formuel-attachment"><?php echo esc_html__('Attachment', 'formuel'); ?></label>
            <input type="file" id="formuel-attachment" name="formuel_attachment" />
        </div>

        <div class="formuel-field">
            <label for="formuel-message"><?php echo esc_html__('Message', 'formuel'); ?></label>
            <textarea id="formuel-message" name="formuel_message" rows="5" required><?php echo esc_textarea($values['message']); ?></textarea>
            <?php if (!empty($errors['message'])) : ?>
                <p class="formuel-field__error"><?php echo esc_html($errors['message']); ?></p>
            <?php endif; ?>
        </div>

        <input type="hidden" name="formuel_time" value="<?php echo esc_attr($timestamp); ?>" />
        <?php wp_nonce_field(Formuel_Shortcode::NONCE_ACTION, 'formuel_nonce'); ?>
        <button class="formuel-button" type="submit" name="formuel_submit" value="1">
            <?php echo esc_html__('Send', 'formuel'); ?>
        </button>
    </form>
</div>
