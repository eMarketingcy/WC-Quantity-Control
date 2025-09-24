<?php
if (!defined('ABSPATH')) exit;
/** @var array $settings */
$nonce = wp_create_nonce('wc_qc_admin_save');

$min  = max(1, (int)($settings['min'] ?? 1));
$max  = max(1, (int)($settings['max'] ?? 999));
$noMax= !empty($settings['no_max']);
$en   = !empty($settings['enable_global']);
$show = !empty($settings['show_message']);
$msg  = (string)($settings['message'] ?? 'Quantity must be between {min} and {max}.');

$enStep  = !empty($settings['enable_step']);
$step    = max(1, (int)($settings['step'] ?? 1));
$enOrder = !empty($settings['enable_order_step']);
$orderSt = max(1, (int)($settings['order_step'] ?? 0));
$enPack  = !empty($settings['enable_packages']);
$packs   = (string)($settings['packages'] ?? '');
?>
<div class="wrap wc-qc-admin-wrapper">

  <div class="wc-qc-header">
    <h1>Quantity Control</h1>
    <p class="description">Set global limits, steps, packages and messaging. Product-level overrides still win.</p>
  </div>

  <div class="wc-qc-status-grid">
    <div class="wc-qc-status-item">
      <div class="wc-qc-status-icon">✓</div>
      <div class="wc-qc-status-content">
        <h3>WooCommerce detected</h3>
        <p>Validation hooks are active.</p>
      </div>
    </div>
    <div class="wc-qc-status-item">
      <div class="wc-qc-status-icon" style="background:linear-gradient(135deg,#06B6D4,#6366F1)">ℹ</div>
      <div class="wc-qc-status-content">
        <h3>Pro tip</h3>
        <p>Packages override steps (e.g., 100,250,500).</p>
      </div>
    </div>
  </div>

  <div class="wc-qc-content">
    <!-- Global limits -->
    <section class="wc-qc-card" role="region" aria-labelledby="wc-qc-global">
      <header class="wc-qc-card-header">
        <h2 id="wc-qc-global">Global Limits</h2>
        <p>Define site-wide min/max rules. You can set max to ∞.</p>
      </header>

      <form id="wc-qc-admin-form" class="wc-qc-form">
        <input type="hidden" name="action" value="wc_qc_admin_save">
        <input type="hidden" name="nonce" value="<?php echo esc_attr($nonce); ?>">

        <div class="wc-qc-form-group">
          <label class="wc-qc-toggle">
            <input type="checkbox" name="enable_global" <?php checked($en); ?> />
            <span class="wc-qc-toggle-slider" aria-hidden="true"></span>
            <span class="wc-qc-toggle-label">Enable global limits</span>
          </label>
          <span class="wc-qc-help-text">When off, only product-specific rules apply.</span>
        </div>

        <div class="wc-qc-form-row">
          <div class="wc-qc-form-group">
            <label for="wc-qc-min">Minimum quantity</label>
            <input id="wc-qc-min" class="wc-qc-input" type="number" name="min" min="1" step="1" value="<?php echo esc_attr($min); ?>">
          </div>

          <div class="wc-qc-form-group">
            <label for="wc-qc-max">Maximum quantity</label>
            <input id="wc-qc-max" class="wc-qc-input" type="number" name="max" min="1" step="1" value="<?php echo esc_attr($max); ?>" <?php disabled($noMax); ?>>
            <label class="wc-qc-toggle" style="margin-top:6px">
              <input type="checkbox" id="wc-qc-no-max" name="no_max" <?php checked($noMax); ?> />
              <span class="wc-qc-toggle-slider"></span>
              <span class="wc-qc-toggle-label">No maximum (∞)</span>
            </label>
          </div>
        </div>
      </form>
    </section>

    <!-- Messaging -->
    <section class="wc-qc-card" role="region" aria-labelledby="wc-qc-msg">
      <header class="wc-qc-card-header">
        <h2 id="wc-qc-msg">Customer Messaging</h2>
        <p>Show a friendly message near the quantity field.</p>
      </header>

      <div class="wc-qc-form">
        <div class="wc-qc-form-group">
          <label class="wc-qc-toggle">
            <input type="checkbox" id="wc-qc-show" name="show_message" form="wc-qc-admin-form" <?php checked($show); ?> />
            <span class="wc-qc-toggle-slider"></span>
            <span class="wc-qc-toggle-label">Show quantity message</span>
          </label>
        </div>

        <div class="wc-qc-form-group">
          <label for="wc-qc-message">Message template</label>
          <textarea id="wc-qc-message" name="message" form="wc-qc-admin-form" rows="3" class="wc-qc-input" style="max-width:640px;"><?php echo esc_textarea($msg); ?></textarea>
          <span class="wc-qc-help-text">Placeholders: <code class="code">{min}</code>, <code class="code">{max}</code>, <code class="code">{step}</code>, <code class="code">{packages}</code></span>
        </div>

        <div class="wc-qc-info-box" aria-live="polite">
          <div class="wc-qc-info-icon">👁</div>
          <div class="wc-qc-info-content">
            <h3>Live preview</h3>
            <ol>
              <li>Min / Max: <strong id="wc-qc-preview-mm"></strong></li>
              <li>Rendered message: <strong id="wc-qc-preview-msg"></strong></li>
            </ol>
          </div>
        </div>
      </div>
    </section>

    <!-- Steps & Packages -->
    <section class="wc-qc-card" role="region" aria-labelledby="wc-qc-steps">
      <header class="wc-qc-card-header">
        <h2 id="wc-qc-steps">Steps & Packages</h2>
        <p>Guide customers to order in increments or fixed packages.</p>
      </header>

      <div class="wc-qc-form">
        <div class="wc-qc-form-row">
          <div class="wc-qc-form-group">
            <label class="wc-qc-toggle">
              <input type="checkbox" id="wc-qc-enable-step" name="enable_step" form="wc-qc-admin-form" <?php checked($enStep); ?> />
              <span class="wc-qc-toggle-slider"></span>
              <span class="wc-qc-toggle-label">Enable per-item step</span>
            </label>
            <input id="wc-qc-step" class="wc-qc-input" type="number" name="step" form="wc-qc-admin-form" min="1" step="1" value="<?php echo esc_attr($step); ?>">
            <span class="wc-qc-help-text">Examples: 50 → 50,100,150… | Min 100 + Step 50 → 100,150,200…</span>
          </div>

          <div class="wc-qc-form-group">
            <label class="wc-qc-toggle">
              <input type="checkbox" id="wc-qc-enable-order-step" name="enable_order_step" form="wc-qc-admin-form" <?php checked($enOrder); ?> />
              <span class="wc-qc-toggle-slider"></span>
              <span class="wc-qc-toggle-label">Enable cart-level step</span>
            </label>
            <input id="wc-qc-order-step" class="wc-qc-input" type="number" name="order_step" form="wc-qc-admin-form" min="1" step="1" value="<?php echo esc_attr($orderSt); ?>">
            <span class="wc-qc-help-text">Total cart quantity must be multiple of this number (e.g., 10).</span>
          </div>
        </div>

        <div class="wc-qc-form-group">
          <label class="wc-qc-toggle">
            <input type="checkbox" id="wc-qc-enable-packages" name="enable_packages" form="wc-qc-admin-form" <?php checked($enPack); ?> />
            <span class="wc-qc-toggle-slider"></span>
            <span class="wc-qc-toggle-label">Enable fixed package sizes</span>
          </label>
          <input id="wc-qc-packages" class="wc-qc-input" type="text" name="packages" form="wc-qc-admin-form" value="<?php echo esc_attr($packs); ?>" placeholder="100,250,500">
          <span class="wc-qc-help-text">Comma-separated integers. If set, packages override per-item step.</span>
        </div>
      </div>
    </section>

    <!-- Seasonal tip -->
    <section class="wc-qc-card" role="region" aria-labelledby="wc-qc-seasonal">
      <header class="wc-qc-card-header">
        <h2 id="wc-qc-seasonal">Managing Seasonal Items</h2>
        <p>Balance inventory for holiday/summer items by adjusting min & max.</p>
      </header>
      <div class="wc-qc-form">
        <div class="wc-qc-info-box">
          <div class="wc-qc-info-icon">🎯</div>
          <div class="wc-qc-info-content">
            <p>For seasonal peaks, raise <strong>minimums</strong> (e.g., 5 per order) to keep orders efficient. Lower or set <strong>No maximum</strong> off-season to clear stock.</p>
          </div>
        </div>
      </div>
    </section>
  </div>

  <!-- Sticky actions -->
  <div class="wc-qc-card wc-qc-form-actions">
    <button id="wc-qc-save" class="wc-qc-btn wc-qc-btn-primary">
      <span class="wc-qc-btn-text">Save changes</span>
      <span class="wc-qc-btn-loading">Saving…</span>
    </button>
    <button id="wc-qc-reset" class="wc-qc-btn">Reset to defaults</button>
    <div id="wc-qc-flash" style="margin-left:8px;"></div>
  </div>
</div>
