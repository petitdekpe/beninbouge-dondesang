<?php

namespace App\Translation;

final class Dictionary
{
    public static function forLang(string $lang): array
    {
        return $lang === 'en' ? self::en() : self::fr();
    }

    private static function fr(): array
    {
        return [
            'nav' => ['donate' => 'Faire un don', 'campaign' => 'La campagne', 'partners' => 'Partenaires', 'info' => 'Infos pratiques'],
            'hero' => [
                'badge' => '2 ans de Bénin Bouge',
                't1' => 'Deux ans à', 't2' => 'faire bouger', 't3' => 'le Bénin.',
                'lead' => 'Pour souffler nos 2 bougies, on transforme l’énergie en solidarité. Soutenez la 2ᵉ édition de « Sang Donné, Vies Sauvées » et aidez-nous à sauver des vies.',
                'cta' => 'Je fais un don', 'cta2' => 'Découvrir la campagne',
                'dateLabel' => 'Date', 'date' => 'Sam. 11 juillet 2026',
                'placeLabel' => 'Lieu', 'place' => 'EYA Centre, Akpakpa',
                'edition' => '2ᵉ édition · en partenariat avec l’ANTS & l’ADSBB',
            ],
            'statsNote' => 'Chiffres indicatifs — à valider avec l’équipe Bénin Bouge.',
            'camp' => [
                'kicker' => 'Sang Donné, Vies Sauvées',
                'title' => 'Et si vous deveniez donneur ?',
                'lead' => 'Au Bénin, les besoins en sang sont constants et les réserves trop souvent insuffisantes. Avec l’ANTS et l’ADSBB, nous mobilisons la communauté pour une collecte qui compte.',
                'cta' => 'Soutenir la collecte',
            ],
            'give' => [
                'kicker' => 'Collecte de fonds',
                'title' => 'Votre don finance la collecte',
                'lead' => 'Chaque contribution couvre la logistique de l’événement : matériel, restauration des donneurs, communication et goodies. 100 % au service de la cause.',
                'choose' => 'Choisissez un montant',
                'free' => 'Autre', 'freePh' => 'Montant libre',
                'ctaPrefix' => 'Donner', 'secured' => 'Paiement sécurisé via FedaPay · Mobile Money & carte',
            ],
            'spons' => [
                'kicker' => 'Appel à sponsors & partenaires',
                'title' => 'Associez votre marque à un geste qui sauve',
                'lead' => 'Visibilité média, impact social et image de marque engagée : rejoignez l’aventure comme partenaire de la 2ᵉ édition.',
                'cta' => 'Devenir partenaire',
                'needsTitle' => 'Nous avons besoin de',
                'needs' => ['Rafraîchissements & restauration', 'Espace média (TV, Radio, Digital, Affichage)', 'Impression de supports de communication', 'Production de goodies (bracelets, t-shirts…)', 'Soutien financier à la collecte'],
            ],
            'partners' => [
                'kicker' => 'Ils nous accompagnent',
                'title' => 'Partenaires officiels',
                'lead' => 'La campagne est menée main dans la main avec les acteurs de référence de la transfusion sanguine au Bénin.',
            ],
            'info' => [
                'kicker' => 'Rendez-vous',
                'title' => 'Infos pratiques',
                'dateLabel' => 'Date', 'dateVal' => 'Samedi 11 juillet 2026', 'timeVal' => 'À partir de 9h00',
                'placeLabel' => 'Lieu', 'placeVal' => 'EYA Centre Communautaire', 'placeSub' => 'Akpakpa, Cotonou',
                'phoneLabel' => 'Téléphone', 'contactLabel' => 'Contact',
            ],
            'faqKicker' => 'Vous vous demandez',
            'faqTitle' => 'Questions fréquentes',
            'faq' => [
                ['q' => 'Qui peut donner son sang ?', 'a' => 'Toute personne en bonne santé, âgée de 18 à 60 ans et pesant au moins 50 kg. Il suffit de se présenter le jour J muni d’une pièce d’identité.'],
                ['q' => 'Le don de sang est-il douloureux ?', 'a' => 'Non. Le prélèvement dure environ 8 à 10 minutes et reste très bien toléré. Une collation est offerte juste après pour récupérer.'],
                ['q' => 'À quoi sert mon don financier ?', 'a' => 'Il finance entièrement l’organisation de la collecte : matériel médical, restauration des donneurs, communication et goodies. Aucun frais caché.'],
                ['q' => 'Le paiement est-il sécurisé ?', 'a' => 'Oui. Les paiements sont traités par FedaPay, qui prend en charge Mobile Money (MTN, Moov, Celtiis) et les cartes bancaires de façon sécurisée.'],
                ['q' => 'Puis-je donner sans venir à l’événement ?', 'a' => 'Absolument. Vous pouvez soutenir la cause en faisant un don en ligne, même sans pouvoir vous déplacer le 11 juillet.'],
            ],
            'footerCta' => ['title' => 'Un geste. Une vie. Un avenir.', 'lead' => 'Rejoignez les centaines de personnes qui font de Bénin Bouge un média qui transforme. Votre don compte.', 'btn' => 'Je soutiens la campagne'],
            'footerTagline' => 'Bénin Bouge, le média du Bénin qui se transforme et qui bouge.',
            'footerRights' => 'Tous droits réservés.',
            'm' => [
                'title' => 'Faire un don', 'step1' => 'Montant', 'step2' => 'Vous', 'step3' => 'Paiement',
                'amountTitle' => 'Quel montant ?', 'continue' => 'Continuer', 'back' => 'Retour',
                'infoTitle' => 'Vos coordonnées',
                'nameLabel' => 'Nom complet', 'namePh' => 'Ex. Awa Koudjo',
                'emailLabel' => 'Email', 'emailPh' => 'vous@email.com',
                'phoneLabel' => 'Téléphone', 'phonePh' => '+229 ...',
                'anon' => 'Faire un don anonyme',
                'payTitle' => 'Mode de paiement', 'youGive' => 'Vous donnez',
                'fedaSecured' => 'Sécurisé par FedaPay', 'payNow' => 'Payer',
                'thanksTitle' => 'Merci infiniment !', 'thanksLead' => 'Votre don a bien été pris en compte. Un reçu vous sera envoyé par email. Ensemble, on sauve des vies.',
                'yourGift' => 'Votre don', 'close' => 'Fermer',
            ],
            'statsList' => [
                ['n' => '+150', 'l' => 'articles publiés'], ['n' => '+50K', 'l' => 'abonnés'], ['n' => '120+', 'l' => 'poches collectées'], ['n' => '2', 'l' => 'éditions solidaires'],
            ],
            'campPoints' => [
                ['icon' => '✚', 't' => 'Un besoin vital', 'd' => 'Le sang ne se fabrique pas : seuls les dons permettent de soigner accidentés, femmes en couches et malades.'],
                ['icon' => '⏱', 't' => '10 minutes pour sauver une vie', 'd' => 'Un don rapide, encadré par des professionnels de l’ANTS, peut sauver jusqu’à 3 vies.'],
                ['icon' => '🤝', 't' => 'Une mobilisation collective', 'd' => 'Donneurs, bénévoles, partenaires et donateurs : chacun a un rôle dans cette chaîne de solidarité.'],
            ],
            'partnersList' => [
                ['abbr' => 'ANTS', 'name' => 'Agence Nationale pour la Transfusion Sanguine', 'role' => 'Encadrement médical et collecte du sang.'],
                ['abbr' => 'ADSBB', 'name' => 'Association des Donneurs de Sang Bénévoles du Bénin', 'role' => 'Mobilisation et sensibilisation des donneurs.'],
                ['abbr' => 'JMDS', 'name' => 'Journée Mondiale du Donneur de Sang 2026', 'role' => 'Cadre national de la campagne.'],
            ],
        ];
    }

    private static function en(): array
    {
        return [
            'nav' => ['donate' => 'Donate', 'campaign' => 'The campaign', 'partners' => 'Partners', 'info' => 'Practical info'],
            'hero' => [
                'badge' => '2 years of Bénin Bouge',
                't1' => 'Two years', 't2' => 'moving', 't3' => 'Benin forward.',
                'lead' => 'To blow out our 2 candles, we’re turning energy into solidarity. Support the 2nd edition of "Blood Given, Lives Saved" and help us save lives.',
                'cta' => 'Donate now', 'cta2' => 'Discover the campaign',
                'dateLabel' => 'Date', 'date' => 'Sat. July 11, 2026',
                'placeLabel' => 'Venue', 'place' => 'EYA Center, Akpakpa',
                'edition' => '2nd edition · with ANTS & ADSBB',
            ],
            'statsNote' => 'Indicative figures — to be confirmed with the Bénin Bouge team.',
            'camp' => [
                'kicker' => 'Blood Given, Lives Saved',
                'title' => 'What if you became a donor?',
                'lead' => 'In Benin, the need for blood is constant and reserves too often run low. With ANTS and ADSBB, we mobilize the community for a collection that matters.',
                'cta' => 'Support the drive',
            ],
            'give' => [
                'kicker' => 'Fundraising',
                'title' => 'Your gift funds the drive',
                'lead' => 'Every contribution covers the event logistics: supplies, donor refreshments, communication and goodies. 100% for the cause.',
                'choose' => 'Choose an amount',
                'free' => 'Other', 'freePh' => 'Custom amount',
                'ctaPrefix' => 'Give', 'secured' => 'Secure payment via FedaPay · Mobile Money & card',
            ],
            'spons' => [
                'kicker' => 'Call for sponsors & partners',
                'title' => 'Tie your brand to a life-saving gesture',
                'lead' => 'Media visibility, social impact and a committed brand image: join us as a partner of the 2nd edition.',
                'cta' => 'Become a partner',
                'needsTitle' => 'We need',
                'needs' => ['Refreshments & catering', 'Media space (TV, Radio, Digital, Billboards)', 'Printing of communication materials', 'Goodies production (bracelets, t-shirts…)', 'Financial support for the drive'],
            ],
            'partners' => [
                'kicker' => 'They support us',
                'title' => 'Official partners',
                'lead' => 'The campaign is run hand in hand with Benin’s leading blood transfusion stakeholders.',
            ],
            'info' => [
                'kicker' => 'Save the date',
                'title' => 'Practical info',
                'dateLabel' => 'Date', 'dateVal' => 'Saturday, July 11, 2026', 'timeVal' => 'From 9:00 AM',
                'placeLabel' => 'Venue', 'placeVal' => 'EYA Community Center', 'placeSub' => 'Akpakpa, Cotonou',
                'phoneLabel' => 'Phone', 'contactLabel' => 'Contact',
            ],
            'faqKicker' => 'You may wonder',
            'faqTitle' => 'Frequently asked questions',
            'faq' => [
                ['q' => 'Who can donate blood?', 'a' => 'Anyone in good health, aged 18 to 60 and weighing at least 50 kg. Just show up on the day with an ID document.'],
                ['q' => 'Does donating blood hurt?', 'a' => 'No. The donation takes about 8 to 10 minutes and is very well tolerated. A snack is offered right after to recover.'],
                ['q' => 'What does my financial gift fund?', 'a' => 'It fully funds the organization of the drive: medical supplies, donor refreshments, communication and goodies. No hidden fees.'],
                ['q' => 'Is the payment secure?', 'a' => 'Yes. Payments are processed by FedaPay, which handles Mobile Money (MTN, Moov, Celtiis) and bank cards securely.'],
                ['q' => 'Can I give without attending the event?', 'a' => 'Absolutely. You can support the cause with an online donation, even if you can’t make it on July 11.'],
            ],
            'footerCta' => ['title' => 'One gesture. One life. One future.', 'lead' => 'Join the hundreds of people making Bénin Bouge a media that transforms. Your gift counts.', 'btn' => 'I support the campaign'],
            'footerTagline' => 'Bénin Bouge, the media of a Benin that transforms and moves.',
            'footerRights' => 'All rights reserved.',
            'm' => [
                'title' => 'Make a donation', 'step1' => 'Amount', 'step2' => 'You', 'step3' => 'Payment',
                'amountTitle' => 'How much?', 'continue' => 'Continue', 'back' => 'Back',
                'infoTitle' => 'Your details',
                'nameLabel' => 'Full name', 'namePh' => 'e.g. Awa Koudjo',
                'emailLabel' => 'Email', 'emailPh' => 'you@email.com',
                'phoneLabel' => 'Phone', 'phonePh' => '+229 ...',
                'anon' => 'Donate anonymously',
                'payTitle' => 'Payment method', 'youGive' => 'You give',
                'fedaSecured' => 'Secured by FedaPay', 'payNow' => 'Pay',
                'thanksTitle' => 'Thank you so much!', 'thanksLead' => 'Your donation has been received. A receipt will be sent to your email. Together, we save lives.',
                'yourGift' => 'Your gift', 'close' => 'Close',
            ],
            'statsList' => [
                ['n' => '+150', 'l' => 'articles published'], ['n' => '+50K', 'l' => 'followers'], ['n' => '120+', 'l' => 'blood bags collected'], ['n' => '2', 'l' => 'solidarity editions'],
            ],
            'campPoints' => [
                ['icon' => '✚', 't' => 'A vital need', 'd' => 'Blood cannot be manufactured: only donations can treat accident victims, mothers in labor and patients.'],
                ['icon' => '⏱', 't' => '10 minutes to save a life', 'd' => 'A quick donation, supervised by ANTS professionals, can save up to 3 lives.'],
                ['icon' => '🤝', 't' => 'A collective effort', 'd' => 'Donors, volunteers, partners and contributors: everyone has a role in this chain of solidarity.'],
            ],
            'partnersList' => [
                ['abbr' => 'ANTS', 'name' => 'National Agency for Blood Transfusion', 'role' => 'Medical supervision and blood collection.'],
                ['abbr' => 'ADSBB', 'name' => 'Benin Voluntary Blood Donors Association', 'role' => 'Donor mobilization and awareness.'],
                ['abbr' => 'JMDS', 'name' => 'World Blood Donor Day 2026', 'role' => 'National framework of the campaign.'],
            ],
        ];
    }
}
