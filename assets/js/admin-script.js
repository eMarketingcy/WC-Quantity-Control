(function($){
  'use strict';

  var $form, $save, $reset, $flash,
      $min, $max, $noMax, $msg, $show,
      $mmOut, $msgOut,
      $enStep, $step, $enOrder, $orderStep, $enPack, $packages;

  function setLoading($btn, on){ $btn.toggleClass('loading', !!on).prop('disabled', !!on); }
  function flash(type, text){
    var cls = type === 'error' ? 'wc-qc-message wc-qc-message-error' : 'wc-qc-message wc-qc-message-success';
    $flash.html('<div class="'+cls+'">'+ text +'</div>');
    setTimeout(function(){ $flash.empty(); }, 4000);
  }
  function intval(v, d){ v = parseInt(v,10); return Number.isFinite(v) && v>0 ? v : d; }

  function renderPreview(){
    var min = intval($min.val(), 1),
        max = $noMax.is(':checked') ? '∞' : intval($max.val(), 999),
        tpl = ($msg.val() || '').toString(),
        step = intval($step.val(), 1),
        packs = ($packages.val() || '').replace(/\s+/g,'');
    $mmOut.text(min + ' / ' + max);
    tpl = tpl.replaceAll('{min}', min).replaceAll('{max}', max).replaceAll('{step}', step).replaceAll('{packages}', packs || '—');
    $msgOut.text(tpl);
  }

  function syncMaxState(){
    var on = $noMax.is(':checked');
    $max.prop('disabled', on).closest('.wc-qc-form-group').toggleClass('is-disabled', on);
  }

  function collectPayload(){
    return {
      action: 'wc_qc_admin_save',
      nonce: $form.find('[name="nonce"]').val(),

      enable_global: $form.find('[name="enable_global"]').is(':checked') ? 'yes':'no',
      no_max: $noMax.is(':checked') ? 'yes':'no',
      min: intval($min.val(), 1),
      max: intval($max.val(), 999),

      show_message: $show.is(':checked') ? 'yes':'no',
      message: $msg.val(),

      enable_step: $enStep.is(':checked') ? 'yes':'no',
      step: intval($step.val(), 1),

      enable_order_step: $enOrder.is(':checked') ? 'yes':'no',
      order_step: intval($orderStep.val(), 0),

      enable_packages: $enPack.is(':checked') ? 'yes':'no',
      packages: ($packages.val() || '').toString()
    };
  }

  function save(e){
    e.preventDefault();
    setLoading($save, true);
    $.post(ajaxurl, collectPayload())
      .done(function(res){ res && res.success ? flash('success','Settings saved.') : flash('error', (res && res.data) || 'Save failed.'); })
      .fail(function(){ flash('error','Network error.'); })
      .always(function(){ setLoading($save, false); });
  }

  function resetDefaults(e){
    e.preventDefault();
    $form.find('[name="enable_global"]').prop('checked', true);
    $noMax.prop('checked', false);
    $min.val(1); $max.val(999);
    $show.prop('checked', true);
    $msg.val('Quantity must be between {min} and {max}.');

    $enStep.prop('checked', false); $step.val(1);
    $enOrder.prop('checked', false); $orderStep.val(0);
    $enPack.prop('checked', false); $packages.val('');

    syncMaxState(); renderPreview();
    flash('success','Defaults restored (not saved).');
  }

  $(function(){
    $form = $('#wc-qc-admin-form');
    $save = $('#wc-qc-save'); $reset = $('#wc-qc-reset'); $flash = $('#wc-qc-flash');

    $min = $('#wc-qc-min'); $max = $('#wc-qc-max'); $noMax = $('#wc-qc-no-max');
    $show = $('#wc-qc-show'); $msg = $('#wc-qc-message');
    $mmOut = $('#wc-qc-preview-mm'); $msgOut = $('#wc-qc-preview-msg');

    $enStep = $('#wc-qc-enable-step'); $step = $('#wc-qc-step');
    $enOrder= $('#wc-qc-enable-order-step'); $orderStep = $('#wc-qc-order-step');
    $enPack = $('#wc-qc-enable-packages'); $packages = $('#wc-qc-packages');

    $min.add($max).on('input change', renderPreview);
    $noMax.on('change', function(){ syncMaxState(); renderPreview(); });
    $msg.on('input', renderPreview);
    $step.add($packages).add($orderStep).on('input change', renderPreview);

    renderPreview(); syncMaxState();

    $save.on('click', save);
    $reset.on('click', resetDefaults);
  });
})(jQuery);
