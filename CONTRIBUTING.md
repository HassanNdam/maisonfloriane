# Contribuer : git flow

Le dépôt suit [git flow](https://github.com/petervanderdoes/gitflow-avh) (extension `git flow`, incluse dans Git pour Windows).

| Branche | Rôle |
| --- | --- |
| `main` | Production (maisonfloriane.com). Ne reçoit que des `release/*` et des `hotfix/*`, chaque fusion est taguée `vX.Y.Z`. |
| `develop` | Intégration : branche par défaut du travail quotidien. |
| `feature/<sujet>` | Une fonctionnalité, partie de `develop` et fusionnée dans `develop`. |
| `release/<X.Y.Z>` | Préparation d'une mise en ligne, depuis `develop`, puis fusion dans `main` et `develop`. |
| `hotfix/<X.Y.Z>` | Correction urgente de la production, depuis `main`, puis fusion dans `main` et `develop`. |

## Après un clone

```bash
git checkout develop
git flow init -d   # reprend la configuration ci-dessous
```

Configuration attendue : production `main`, développement `develop`, préfixes `feature/`, `bugfix/`, `release/`, `hotfix/`, `support/`, tags `v`.

## Au quotidien

```bash
git flow feature start fiche-produit
# … commits …
git flow feature publish fiche-produit   # pousse la branche, puis pull request vers develop
git flow feature finish fiche-produit

git flow release start 1.0.0
git flow release finish 1.0.0            # fusionne dans main et develop, tag v1.0.0
git push origin main develop --tags

git flow hotfix start 1.0.1
git flow hotfix finish 1.0.1
git push origin main develop --tags
```

## Rythme des commits et des push

Règle du projet : **rien ne reste seulement en local**.

| Moment | Action |
| --- | --- |
| Début d'une fonctionnalité | `git flow feature start <sujet>` depuis `develop` |
| Chaque étape qui fonctionne | commit (petit, une idée par commit), seulement si le site répond et que `npm run build` passe |
| Premier commit de la branche | `git flow feature publish <sujet>` : la branche est sauvegardée sur GitHub |
| Commits suivants | `git push` |
| Fonctionnalité terminée et vérifiée | `git flow feature finish <sujet>` puis `git push origin develop` (supprimer la branche distante : `git push origin --delete feature/<sujet>`) |
| Mise en ligne | **sur décision explicite seulement** : `git flow release` → `main` + tag `vX.Y.Z` + `CHANGELOG.md` |

Avant chaque push, vérifier qu'aucun secret n'est indexé : `.env`, clés Stripe (`sk_…`, `pk_…`, `whsec_…`), clé Germanized dans `web/wp-config.php`.

## Messages de commit

[Conventional Commits](https://www.conventionalcommits.org/fr/), en français : `feat(theme): fiche produit`, `fix(woocommerce): seuil de stock`, `docs: cadrage`, `chore(deps): mise à jour WooCommerce`.

Portées utiles : `theme`, `woocommerce`, `seed`, `config`, `docs`, `deps`.

## Ce qui ne va jamais dans le dépôt

`.env`, `vendor/`, `node_modules/`, `web/wp/`, `web/app/plugins/` (Composer), `web/app/uploads/`, `public/build/` du thème. Les originaux des médias restent hors dépôt (dossier `99-sources-originales` du projet).
