{{-- Message après envoi d'un formulaire (?formulaire=…). $succes : statut de réussite, $message : texte affiché. --}}
@php($statut = sanitize_key($_GET['formulaire'] ?? ''))
@if ($statut === $succes)
  <p class="statut statut--ok" role="status">{{ $message }}</p>
@elseif ($statut === 'incomplet')
  <p class="statut statut--erreur" role="alert">Merci de vérifier les champs obligatoires.</p>
@elseif ($statut === 'erreur')
  <p class="statut statut--erreur" role="alert">L'envoi n'a pas fonctionné. Réessayez ou écrivez-nous sur WhatsApp.</p>
@endif
