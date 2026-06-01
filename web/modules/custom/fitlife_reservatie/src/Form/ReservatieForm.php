<?php

namespace Drupal\fitlife_reservatie\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;
use Drupal\Core\Render\Markup;

class ReservatieForm extends FormBase {

  public function getFormId() {
    return 'fitlife_reservatie_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state, $les_id = NULL) {
    $db = Database::getConnection();
    $les = $db->select('fitlife_lessen', 'l')
      ->fields('l')
      ->condition('id', $les_id)
      ->execute()
      ->fetchObject();

    if (!$les) {
      $form['error'] = ['#markup' => '<p>Les niet gevonden.</p>'];
      return $form;
    }

    // Verberg de paginatitel.
    $form['#title'] = '';

    $images = [
      'Spinning' => 'https://images.unsplash.com/photo-1534787238916-9ba6764efd4f?w=900',
      'Yoga' => 'https://images.unsplash.com/photo-1575052814086-f385e2e2ad1b?w=900',
      'CrossFit' => 'https://images.unsplash.com/photo-1534258936925-c58bed479fcb?w=900',
      'Pilates' => 'https://images.unsplash.com/photo-1518611012118-696072aa579a?w=900',
      'Boxing' => 'https://images.unsplash.com/photo-1549719386-74dfcbf7dbed?w=900',
      'Zumba' => 'https://images.unsplash.com/photo-1524594152303-9fd13543fe6e?w=900',
    ];
    $icons = ['Spinning'=>'🚴','Yoga'=>'🧘','CrossFit'=>'🏋️','Pilates'=>'🤸','Boxing'=>'🥊','Zumba'=>'💃'];
    $img = $images[$les->naam] ?? 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=900';
    $icon = $icons[$les->naam] ?? '💪';

    $reservaties = $db->select('fitlife_reservaties', 'r')
      ->condition('les_id', $les_id)
      ->countQuery()->execute()->fetchField();
    $vrij = $les->capaciteit - $reservaties;
    $percent = round(($reservaties / $les->capaciteit) * 100);
    $bar_color = $percent >= 80 ? '#e63946' : ($percent >= 50 ? '#ff9f1c' : '#2ec4b6');

    $header = '<div style="margin:-25px -20px 0;font-family:system-ui,-apple-system,sans-serif;">
      <div style="position:relative;height:280px;overflow:hidden;">
        <img src="'.$img.'" style="width:100%;height:100%;object-fit:cover;display:block;">
        <div style="position:absolute;inset:0;background:linear-gradient(to bottom,rgba(26,26,46,0.4),rgba(26,26,46,0.85));"></div>
        <div style="position:absolute;bottom:30px;left:0;right:0;text-align:center;color:#fff;">
          <div style="font-size:3.5rem;line-height:1;">'.$icon.'</div>
          <h1 style="color:#fff;font-size:2.6rem;font-weight:900;margin:10px 0 0;">'.$les->naam.'</h1>
        </div>
      </div>
      <div style="background:linear-gradient(180deg,#f0f2f5,#e4e8ee);padding:40px 20px;">
        <div style="max-width:560px;margin:0 auto;">
          <div style="background:#fff;border-radius:16px;box-shadow:0 8px 30px rgba(0,0,0,0.1);padding:28px;">
            <div style="display:flex;justify-content:space-between;padding:12px 0;border-bottom:1px solid #f0f0f0;">
              <span style="color:#888;">👨‍🏫 Coach</span><strong style="color:#1a1a2e;">'.$les->coach.'</strong>
            </div>
            <div style="display:flex;justify-content:space-between;padding:12px 0;border-bottom:1px solid #f0f0f0;">
              <span style="color:#888;">📅 Datum</span><strong style="color:#1a1a2e;">'.$les->datum.'</strong>
            </div>
            <div style="display:flex;justify-content:space-between;padding:12px 0;border-bottom:1px solid #f0f0f0;">
              <span style="color:#888;">⏰ Tijdstip</span><strong style="color:#1a1a2e;">'.$les->tijdstip.'</strong>
            </div>
            <div style="padding:16px 0 4px;">
              <div style="display:flex;justify-content:space-between;font-size:0.8rem;color:#888;margin-bottom:6px;font-weight:700;">
                <span>BESCHIKBAARHEID</span><span>'.$vrij.'/'.$les->capaciteit.' plaatsen vrij</span>
              </div>
              <div style="background:#eceff1;border-radius:10px;height:10px;overflow:hidden;">
                <div style="background:'.$bar_color.';width:'.$percent.'%;height:10px;"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>';

    $form['header'] = ['#markup' => Markup::create($header)];

    $form['les_id'] = ['#type' => 'hidden', '#value' => $les_id];

    if ($vrij > 0) {
      $form['submit'] = [
        '#type' => 'submit',
        '#value' => '✓ Bevestig reservatie',
        '#attributes' => ['style' => 'background:#e63946;border:none;color:#fff;padding:14px 40px;border-radius:30px;font-weight:700;font-size:1.05rem;box-shadow:0 4px 16px rgba(230,57,70,0.4);cursor:pointer;'],
        '#prefix' => '<div style="display:flex;justify-content:center;margin:30px 0;">',
        '#suffix' => '</div>',
      ];
    } else {
      $form['vol'] = [
        '#markup' => '<div style="text-align:center;margin:30px 0;"><span style="background:#6c757d;color:#fff;padding:14px 28px;border-radius:30px;font-weight:700;">Deze les is helaas volzet.</span></div>',
      ];
    }

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $db = Database::getConnection();
    $uid = \Drupal::currentUser()->id();
    $les_id = $form_state->getValue('les_id');

    $bestaand = $db->select('fitlife_reservaties', 'r')
      ->condition('les_id', $les_id)
      ->condition('uid', $uid)
      ->countQuery()->execute()->fetchField();

    if ($bestaand) {
      \Drupal::messenger()->addWarning('Je hebt deze les al gereserveerd.');
    } else {
      $db->insert('fitlife_reservaties')->fields([
        'les_id' => $les_id,
        'uid' => $uid,
        'created' => \Drupal::time()->getRequestTime(),
      ])->execute();
      \Drupal::messenger()->addStatus('Reservatie succesvol!');
    }

    $form_state->setRedirect('fitlife_reservatie.mijn_reservaties');
  }
}
