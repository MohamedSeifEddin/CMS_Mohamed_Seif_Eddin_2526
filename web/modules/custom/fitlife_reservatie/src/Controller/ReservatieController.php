<?php

namespace Drupal\fitlife_reservatie\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Drupal\Core\Url;
use Drupal\Core\Render\Markup;

class ReservatieController extends ControllerBase {

  public function lessen() {
    $db = Database::getConnection();
    $uid = \Drupal::currentUser()->id();
    $lessen = $db->select('fitlife_lessen', 'l')->fields('l')->execute()->fetchAll();
    $is_admin = in_array('administrator', \Drupal::currentUser()->getRoles(), TRUE);

    $images = [
      'Spinning' => 'https://images.unsplash.com/photo-1534787238916-9ba6764efd4f?w=600',
      'Yoga' => 'https://images.unsplash.com/photo-1575052814086-f385e2e2ad1b?w=600',
      'CrossFit' => 'https://images.unsplash.com/photo-1534258936925-c58bed479fcb?w=600',
      'Pilates' => 'https://images.unsplash.com/photo-1518611012118-696072aa579a?w=600',
      'Boxing' => 'https://images.unsplash.com/photo-1549719386-74dfcbf7dbed?w=600',
      'Zumba' => 'https://images.unsplash.com/photo-1524594152303-9fd13543fe6e?w=600',
    ];
    $default_img = 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=600';
    $icons = ['Spinning'=>'🚴','Yoga'=>'🧘','CrossFit'=>'🏋️','Pilates'=>'🤸','Boxing'=>'🥊','Zumba'=>'💃'];

    $cards = '';
    foreach ($lessen as $les) {
      $reservaties = $db->select('fitlife_reservaties', 'r')->condition('les_id', $les->id)->countQuery()->execute()->fetchField();
      $vrij = $les->capaciteit - $reservaties;
      $icon = $icons[$les->naam] ?? '💪';
      // Geüploade foto heeft voorrang; anders naam-gebaseerd of default.
      $img = $images[$les->naam] ?? $default_img;
      if (!empty($les->foto_fid)) {
        $file = \Drupal\file\Entity\File::load($les->foto_fid);
        if ($file) {
          $img = \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri());
        }
      }
      $percent = round(($reservaties / $les->capaciteit) * 100);
      $bar_color = $percent >= 80 ? '#e63946' : ($percent >= 50 ? '#ff9f1c' : '#2ec4b6');

      $al = FALSE;
      if ($uid) {
        $al = $db->select('fitlife_reservaties', 'r')->condition('les_id', $les->id)->condition('uid', $uid)->countQuery()->execute()->fetchField();
      }

      if ($al) {
        $u = Url::fromRoute('fitlife_reservatie.uitschrijven', ['les_id' => $les->id])->toString();
        $actie = '<span style="background:#2ec4b6;color:#fff;padding:9px 14px;border-radius:30px;font-size:0.8rem;font-weight:700;">✓ Gereserveerd</span> <a href="'.$u.'" style="background:#e63946;color:#fff;padding:9px 14px;border-radius:30px;font-size:0.8rem;font-weight:700;text-decoration:none;">Uitschrijven</a>';
      } elseif ($vrij > 0) {
        $r = Url::fromRoute('fitlife_reservatie.reserveer', ['les_id' => $les->id])->toString();
        $actie = '<a href="'.$r.'" style="background:#e63946;color:#fff;padding:12px 30px;border-radius:30px;font-weight:700;text-decoration:none;font-size:0.95rem;display:inline-block;box-shadow:0 4px 14px rgba(230,57,70,0.4);">Reserveer nu →</a>';
      } else {
        $actie = '<span style="background:#6c757d;color:#fff;padding:9px 16px;border-radius:30px;font-size:0.85rem;">Volzet</span>';
      }

      // Coachnaam: nieuwe lessen gebruiken coach_uid, oude het tekstveld.
      $coach_naam = $les->coach;
      if (!empty($les->coach_uid)) {
        $cu = \Drupal\user\Entity\User::load($les->coach_uid);
        if ($cu) {
          $coach_naam = $cu->getDisplayName();
        }
      }
      // Beheerknop alleen voor admins.
      $beheer = '';
      $mag_beheren = $is_admin || ((int) $les->coach_uid === (int) \Drupal::currentUser()->id());
      if ($mag_beheren) {
        $bu = Url::fromRoute('fitlife_reservatie.les_beheren', ['les_id' => $les->id])->toString();
        $beheer = '<div style="text-align:center;margin-top:12px;"><a href="' . $bu . '" style="display:inline-flex;align-items:center;gap:6px;background:#1a1a2e;color:#fff;padding:9px 20px;border-radius:30px;font-size:0.82rem;font-weight:700;text-decoration:none;transition:background 0.15s;">Beheren</a></div>';
      }
      $cards .= '<div style="background:#fff;border-radius:18px;overflow:hidden;box-shadow:0 8px 30px rgba(0,0,0,0.12);width:330px;display:flex;flex-direction:column;">
        <div style="position:relative;height:200px;">
          <img src="'.$img.'" style="width:100%;height:100%;object-fit:cover;display:block;">
          <div style="position:absolute;inset:0;background:linear-gradient(to bottom,transparent 35%,rgba(0,0,0,0.75));"></div>
          <div style="position:absolute;bottom:16px;left:18px;color:#fff;">
            <div style="font-size:2rem;line-height:1;">'.$icon.'</div>
            <h3 style="color:#fff;margin:6px 0 0;font-size:1.5rem;font-weight:800;">'.$les->naam.'</h3>
          </div>
        </div>
        <div style="padding:22px;display:flex;flex-direction:column;flex:1;">
          <p style="margin:4px 0;color:#444;font-size:1rem;">👨‍🏫 <strong>'.$coach_naam.'</strong></p>
          <p style="margin:4px 0;color:#444;font-size:1rem;">📅 '.$les->datum.' &nbsp;⏰ <strong>'.$les->tijdstip.'</strong></p>
          <div style="margin:16px 0 22px;">
            <div style="display:flex;justify-content:space-between;font-size:0.75rem;color:#888;margin-bottom:6px;font-weight:700;letter-spacing:0.5px;">
              <span>BEZETTING</span><span>'.$vrij.'/'.$les->capaciteit.' vrij</span>
            </div>
            <div style="background:#eceff1;border-radius:10px;height:9px;overflow:hidden;">
              <div style="background:'.$bar_color.';width:'.$percent.'%;height:9px;"></div>
            </div>
          </div>
          <div style="text-align:center;margin-top:auto;">'.$actie.'</div>
          '.$beheer.'
        </div>
      </div>';
    }

    $html = '<div style="margin:-25px -20px 0;font-family:system-ui,-apple-system,sans-serif;">
      <div style="background:linear-gradient(rgba(26,26,46,0.78),rgba(26,26,46,0.85)),url(\'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?w=1600\');background-size:cover;background-position:center;padding:90px 20px;text-align:center;color:#fff;">
        <h1 style="color:#fff;font-size:3rem;font-weight:900;margin:0 0 12px;">🏃 Groepslessen</h1>
        <p style="font-size:1.3rem;color:#e0e0e0;margin:0;">Kies jouw les en reserveer direct online</p>
      </div>
      <div style="background:linear-gradient(180deg,#f0f2f5,#e4e8ee);padding:55px 20px;">
        <div style="max-width:1100px;margin:0 auto;display:flex;flex-wrap:wrap;gap:28px;justify-content:center;">'.$cards.'</div>
      </div>
    </div>';

    return ['#markup' => Markup::create($html), '#cache' => ['max-age' => 0]];
  }

  public function uitschrijven($les_id) {
    $db = Database::getConnection();
    $uid = \Drupal::currentUser()->id();
    $db->delete('fitlife_reservaties')->condition('les_id', $les_id)->condition('uid', $uid)->execute();
    \Drupal::messenger()->addStatus('Je bent uitgeschreven.');
    return $this->redirect('fitlife_reservatie.mijn_reservaties');
  }

  public function mijnReservaties() {
    $db = Database::getConnection();
    $uid = \Drupal::currentUser()->id();
    $reservaties = $db->select('fitlife_reservaties', 'r')->fields('r')->condition('uid', $uid)->execute()->fetchAll();

    $images = [
      'Spinning' => 'https://images.unsplash.com/photo-1534787238916-9ba6764efd4f?w=600',
      'Yoga' => 'https://images.unsplash.com/photo-1575052814086-f385e2e2ad1b?w=600',
      'CrossFit' => 'https://images.unsplash.com/photo-1534258936925-c58bed479fcb?w=600',
      'Pilates' => 'https://images.unsplash.com/photo-1518611012118-696072aa579a?w=600',
      'Boxing' => 'https://images.unsplash.com/photo-1549719386-74dfcbf7dbed?w=600',
      'Zumba' => 'https://images.unsplash.com/photo-1524594152303-9fd13543fe6e?w=600',
    ];
    $default_img = 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=600';
    $icons = ['Spinning'=>'🚴','Yoga'=>'🧘','CrossFit'=>'🏋️','Pilates'=>'🤸','Boxing'=>'🥊','Zumba'=>'💃'];

    $cards = '';
    $aantal = 0;
    foreach ($reservaties as $res) {
      $les = $db->select('fitlife_lessen', 'l')->fields('l')->condition('id', $res->les_id)->execute()->fetchObject();
      if ($les) {
        $aantal++;
        $icon = $icons[$les->naam] ?? '💪';
        // Geüploade foto heeft voorrang; anders naam-gebaseerd of default.
      $img = $images[$les->naam] ?? $default_img;
      if (!empty($les->foto_fid)) {
        $file = \Drupal\file\Entity\File::load($les->foto_fid);
        if ($file) {
          $img = \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri());
        }
      }
        $u = Url::fromRoute('fitlife_reservatie.uitschrijven', ['les_id' => $les->id])->toString();

        // Coachnaam: nieuwe lessen gebruiken coach_uid, oude het tekstveld.
      $coach_naam = $les->coach;
      if (!empty($les->coach_uid)) {
        $cu = \Drupal\user\Entity\User::load($les->coach_uid);
        if ($cu) {
          $coach_naam = $cu->getDisplayName();
        }
      }
      $cards .= '<div style="background:#fff;border-radius:18px;overflow:hidden;box-shadow:0 8px 30px rgba(0,0,0,0.12);width:330px;display:flex;flex-direction:column;">
          <div style="position:relative;height:180px;">
            <img src="'.$img.'" style="width:100%;height:100%;object-fit:cover;display:block;">
            <div style="position:absolute;inset:0;background:linear-gradient(to bottom,transparent 35%,rgba(0,0,0,0.75));"></div>
            <div style="position:absolute;top:14px;right:14px;background:#2ec4b6;color:#fff;padding:6px 14px;border-radius:30px;font-size:0.75rem;font-weight:700;">✓ BEVESTIGD</div>
            <div style="position:absolute;bottom:14px;left:18px;color:#fff;">
              <div style="font-size:1.8rem;line-height:1;">'.$icon.'</div>
              <h3 style="color:#fff;margin:6px 0 0;font-size:1.4rem;font-weight:800;">'.$les->naam.'</h3>
            </div>
          </div>
          <div style="padding:20px;display:flex;flex-direction:column;flex:1;">
            <p style="margin:4px 0;color:#444;font-size:0.95rem;">👨‍🏫 <strong>'.$coach_naam.'</strong></p>
            <p style="margin:4px 0 16px;color:#444;font-size:0.95rem;">📅 '.$les->datum.' &nbsp;⏰ <strong>'.$les->tijdstip.'</strong></p>
            <div style="text-align:center;margin-top:auto;">
              <a href="'.$u.'" style="background:#e63946;color:#fff;padding:10px 26px;border-radius:30px;font-weight:700;text-decoration:none;font-size:0.9rem;display:inline-block;">Annuleer reservatie</a>
            </div>
          </div>
        </div>';
      }
    }

    if ($aantal === 0) {
      $lessen_url = Url::fromRoute('fitlife_reservatie.lessen')->toString();
      $inhoud = '<div style="text-align:center;padding:40px 20px;">
        <div style="font-size:4rem;margin-bottom:10px;">📭</div>
        <h2 style="color:#1a1a2e;margin:0 0 10px;">Nog geen reservaties</h2>
        <p style="color:#666;font-size:1.1rem;margin-bottom:24px;">Je hebt nog geen lessen geboekt. Bekijk het lessenrooster en reserveer jouw eerste les!</p>
        <a href="'.$lessen_url.'" style="background:#e63946;color:#fff;padding:14px 36px;border-radius:30px;font-weight:700;text-decoration:none;font-size:1rem;display:inline-block;box-shadow:0 4px 16px rgba(230,57,70,0.4);">Bekijk groepslessen →</a>
      </div>';
    } else {
      $inhoud = '<div style="max-width:1100px;margin:0 auto;display:flex;flex-wrap:wrap;gap:28px;justify-content:center;">'.$cards.'</div>';
    }

    $html = '<div style="margin:-25px -20px 0;font-family:system-ui,-apple-system,sans-serif;">
      <div style="background:linear-gradient(rgba(26,26,46,0.8),rgba(26,26,46,0.88)),url(\'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?w=1600\');background-size:cover;background-position:center;padding:90px 20px;text-align:center;color:#fff;">
        <h1 style="color:#fff;font-size:3rem;font-weight:900;margin:0 0 12px;">📋 Mijn Reservaties</h1>
        <p style="font-size:1.3rem;color:#e0e0e0;margin:0;">Een overzicht van al jouw geboekte lessen</p>
      </div>
      <div style="background:linear-gradient(180deg,#f0f2f5,#e4e8ee);padding:55px 20px;min-height:40vh;">'.$inhoud.'</div>
    </div>';

    return ['#markup' => Markup::create($html), '#cache' => ['max-age' => 0]];
  }

  /**
   * Toegang tot lesbeheer: admins mogen alles, een coach enkel z'n eigen les.
   */
  public function lesBeheerToegang($les_id) {
    $account = \Drupal::currentUser();
    if (in_array('administrator', $account->getRoles(), TRUE)) {
      return \Drupal\Core\Access\AccessResult::allowed();
    }
    $coach_uid = \Drupal::database()->select('fitlife_lessen', 'l')
      ->fields('l', ['coach_uid'])
      ->condition('id', $les_id)
      ->execute()
      ->fetchField();
    return \Drupal\Core\Access\AccessResult::allowedIf((int) $coach_uid === (int) $account->id());
  }

}
