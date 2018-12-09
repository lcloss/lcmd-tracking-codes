<?php if ( !defined('ABSPATH') ) exit; ?>
<div class="wrap">
    <h1>LC Tracking Codes</h1>
    <h2 class="nav-tab-wrapper">
        <a href="admin.php?page=lcmd-tracking-codes&tab=google" class="nav-tab">Google</a>
        <a href="admin.php?page=lcmd-tracking-codes&tab=bing" class="nav-tab">Bing</a>
    </h2>
    <div class="wrap">
        {% if has_errors %}
        <div class="errors">
            {% for errors %}
            <p class="error_line">{{ errors.text }}</p>
            {% endfor %}
        </div>
        {% endif %}
        {% if tab_google %}
        <h2>Google Tracking Codes</h2>
        <form method="POST" action="">
            <?php settings_fields( 'lcmd_tracking_codes_settings_group' ); ?>
            <input type="hidden" name="tab" value="google">
            {% for google_fields %}
            <div class="form-group">
                <label for="{{ google_fields.name }}">{{ google_fields.label }}</label>
                <input type="text" class="form-control" id="{{ google_fields.id }}" name="{{ google_fields.name }}" value="{{ google_fields.value }}">
                <p class="help">{{ google_fields.help }}</p>
            </div>
            {% endfor %}
            <input type="submit" class="btn btn-primary" value="{{ submit }}">
        </form>
        {% endif %}
        {% if tab_bing %}
        <h2>Bing Tracking Codes</h2>
        <form method="POST">
            <input type="hidden" name="tab" value="bing">
            {% for bing_fields %}
            <div class="form-group">
                <label for="{{ bing_fields.name }}">{{ bing_fields.label }}</label>
                <input type="text" class="form-control" id="{{ bing_fields.id }}" name="{{ bing_fields.name }}" value="{{ bing_fields.value }}">
                <p class="help">{{ bing_fields.help }}</p>
            </div>
            {% endfor %}
            <input type="submit" class="btn btn-primary" value="{{ submit }}">
        </form>
        {% endif %}
    </div>
</div>