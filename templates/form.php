<?php
/** @var array $values */
/** @var array $errors */
/** @var string $message */
/** @var float $base_price */
?>
<div class="formuel-wrapper">
    <?php if ($message === 'success') : ?>
        <div class="formuel-notice formuel-notice--success">
            <?php echo esc_html__('Thanks! Your registration was saved.', 'formuel'); ?>
        </div>
    <?php elseif ($message === 'error') : ?>
        <div class="formuel-notice formuel-notice--error">
            <?php echo esc_html__('Please check the highlighted fields.', 'formuel'); ?>
        </div>
    <?php endif; ?>

    <form class="formuel-form" method="post">
        <?php if ($base_price > 0) : ?>
            <p class="formuel-info">
                <?php echo esc_html(sprintf(__('Base price per day: %s', 'formuel'), number_format_i18n($base_price, 2))); ?>
            </p>
        <?php endif; ?>
        <div class="formuel-field">
            <label for="formuel-participant-name"><?php echo esc_html__('Participant name', 'formuel'); ?></label>
            <input type="text" id="formuel-participant-name" name="formuel_participant_name" value="<?php echo esc_attr($values['participant_name']); ?>" required />
            <?php if (!empty($errors['participant_name'])) : ?>
                <span class="formuel-error"><?php echo esc_html($errors['participant_name']); ?></span>
            <?php endif; ?>
        </div>

        <div class="formuel-field">
            <label for="formuel-guardian-name"><?php echo esc_html__('Guardian name', 'formuel'); ?></label>
            <input type="text" id="formuel-guardian-name" name="formuel_guardian_name" value="<?php echo esc_attr($values['guardian_name']); ?>" required />
            <?php if (!empty($errors['guardian_name'])) : ?>
                <span class="formuel-error"><?php echo esc_html($errors['guardian_name']); ?></span>
            <?php endif; ?>
        </div>

        <div class="formuel-field">
            <label for="formuel-email"><?php echo esc_html__('Email', 'formuel'); ?></label>
            <input type="email" id="formuel-email" name="formuel_email" value="<?php echo esc_attr($values['email']); ?>" required />
            <?php if (!empty($errors['email'])) : ?>
                <span class="formuel-error"><?php echo esc_html($errors['email']); ?></span>
            <?php endif; ?>
        </div>

        <div class="formuel-field">
            <label for="formuel-phone"><?php echo esc_html__('Phone', 'formuel'); ?></label>
            <input type="text" id="formuel-phone" name="formuel_phone" value="<?php echo esc_attr($values['phone']); ?>" />
        </div>

        <div class="formuel-field">
            <label for="formuel-program"><?php echo esc_html__('Program', 'formuel'); ?></label>
            <select id="formuel-program" name="formuel_program" required>
                <option value=""><?php echo esc_html__('Select a program', 'formuel'); ?></option>
                <option value="ferienbetreuung" <?php selected($values['program'], 'ferienbetreuung'); ?>><?php echo esc_html__('Ferienbetreuung', 'formuel'); ?></option>
                <option value="ganztag" <?php selected($values['program'], 'ganztag'); ?>><?php echo esc_html__('Ganztagsangebot', 'formuel'); ?></option>
            </select>
            <?php if (!empty($errors['program'])) : ?>
                <span class="formuel-error"><?php echo esc_html($errors['program']); ?></span>
            <?php endif; ?>
        </div>

        <div class="formuel-field">
            <label for="formuel-days"><?php echo esc_html__('Number of days', 'formuel'); ?></label>
            <input type="number" id="formuel-days" name="formuel_days" min="1" value="<?php echo esc_attr((string) $values['days']); ?>" required />
            <?php if (!empty($errors['days'])) : ?>
                <span class="formuel-error"><?php echo esc_html($errors['days']); ?></span>
            <?php endif; ?>
        </div>

        <div class="formuel-field">
            <label for="formuel-voucher"><?php echo esc_html__('Voucher code (optional)', 'formuel'); ?></label>
            <input type="text" id="formuel-voucher" name="formuel_voucher_code" value="<?php echo esc_attr($values['voucher_code']); ?>" />
        </div>

        <div class="formuel-field">
            <label for="formuel-message"><?php echo esc_html__('Notes', 'formuel'); ?></label>
            <textarea id="formuel-message" name="formuel_message" rows="5" required><?php echo esc_textarea($values['message']); ?></textarea>
            <?php if (!empty($errors['message'])) : ?>
                <span class="formuel-error"><?php echo esc_html($errors['message']); ?></span>
            <?php endif; ?>
        </div>

        <div class="formuel-field formuel-field--hidden">
            <label for="formuel-hp"><?php echo esc_html__('Leave this field empty', 'formuel'); ?></label>
            <input type="text" id="formuel-hp" name="formuel_hp" value="" autocomplete="off" />
        </div>

        <input type="hidden" name="formuel_timestamp" value="<?php echo esc_attr((string) time()); ?>" />
        <?php wp_nonce_field(Formuel_Shortcode::NONCE_ACTION, 'formuel_nonce'); ?>
        <button class="formuel-button" type="submit" name="formuel_submit" value="1">
            <?php echo esc_html__('Submit registration', 'formuel'); ?>
        </button>
    </form>
</div>
