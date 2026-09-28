/**
 * EVASIONS — script du thème.
 *
 * Quatre fonctions : ouvrir et fermer le menu sur mobile, boutons − / + du champ quantité, mise à jour automatique du panier, filtres du listing
 * (panneau mobile, curseur de prix, envoi automatique). Sans jQuery, sans
 * dépendance. Sans JavaScript, le menu reste fermé mais la barre de pastilles
 * (Plage, Camping & Rando, Nouveautés) donne déjà accès aux univers.
 */
( function () {
	'use strict';

	var header = document.querySelector( '.ev-header' );
	var burger = document.querySelector( '.ev-burger' );

	// Champ quantité : boutons − et + (sans JavaScript, le champ numérique natif reste utilisable).
	document.addEventListener( 'click', function ( event ) {
		var button = event.target.closest ? event.target.closest( '[data-ev-step]' ) : null;

		if ( ! button ) {
			return;
		}

		var input = button.parentNode.querySelector( 'input.qty' );

		if ( ! input ) {
			return;
		}

		var step = parseFloat( input.getAttribute( 'step' ) ) || 1;
		var min = input.getAttribute( 'min' ) === '' || input.getAttribute( 'min' ) === null ? 0 : parseFloat( input.getAttribute( 'min' ) );
		var max = input.getAttribute( 'max' ) === '' || input.getAttribute( 'max' ) === null ? Infinity : parseFloat( input.getAttribute( 'max' ) );
		var next = ( parseFloat( input.value ) || 0 ) + step * parseInt( button.getAttribute( 'data-ev-step' ), 10 );

		input.value = Math.min( max, Math.max( min, next ) );
		input.dispatchEvent( new Event( 'change', { bubbles: true } ) );
	} );

	// Panier : la quantité met le panier à jour toute seule, sans bouton « Mettre à jour ».
	var updateTimer = null;

	document.addEventListener( 'change', function ( event ) {
		var input = event.target;

		if ( ! input.matches || ! input.matches( '.woocommerce-cart-form input.qty' ) ) {
			return;
		}

		window.clearTimeout( updateTimer );
		updateTimer = window.setTimeout( function () {
			var button = document.querySelector( '.woocommerce-cart-form button[name="update_cart"]' );

			if ( button ) {
				button.disabled = false;
				button.removeAttribute( 'aria-disabled' );
				button.click();
			}
		}, 600 );
	} );

	// Ce script s'exécute : les règles « avec JavaScript » de la feuille de style s'appliquent.
	document.documentElement.classList.add( 'ev-js' );

	// Filtres du listing : panneau plein écran en mobile, envoi automatique en desktop.
	var filters = document.getElementById( 'ev-filters' );
	var desktop = window.matchMedia( '(min-width: 900px)' );

	if ( filters ) {
		var opener = document.querySelector( '[data-ev-filters-open]' );
		var closer = filters.querySelector( '[data-ev-filters-close]' );
		var form = filters.querySelector( 'form' );

		// Panneau ouvert = fenêtre modale : le reste de la page n'est plus atteignable au clavier ni au lecteur d'écran.
		var isolate = function ( node, on ) {
			Array.prototype.forEach.call( node.children, function ( child ) {
				if ( child === filters ) {
					return;
				}

				if ( child.contains( filters ) ) {
					isolate( child, on );
				} else if ( child.tagName !== 'SCRIPT' ) {
					child.inert = on;
				}
			} );
		};

		var setFilters = function ( open ) {
			filters.classList.toggle( 'is-open', open );
			document.body.style.overflow = open ? 'hidden' : '';
			isolate( document.body, open );

			if ( open ) {
				filters.setAttribute( 'role', 'dialog' );
				filters.setAttribute( 'aria-modal', 'true' );
			} else {
				filters.removeAttribute( 'role' );
				filters.removeAttribute( 'aria-modal' );
			}

			if ( opener ) {
				opener.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
			}

			if ( open && closer ) {
				closer.focus();
			} else if ( ! open && opener && ! desktop.matches ) {
				opener.focus();
			}
		};

		// L'adresse #ev-filters ouvre le panneau sans JavaScript ; ici on la remplace par un état, sans entrée d'historique.
		if ( opener ) {
			opener.addEventListener( 'click', function ( event ) {
				event.preventDefault();
				setFilters( true );
			} );
		}

		if ( closer ) {
			closer.addEventListener( 'click', function ( event ) {
				event.preventDefault();
				setFilters( false );
			} );
		}

		document.addEventListener( 'keydown', function ( event ) {
			if ( event.key === 'Escape' && filters.classList.contains( 'is-open' ) ) {
				setFilters( false );
			}
		} );

		desktop.addEventListener( 'change', function () {
			setFilters( false );
		} );

		// Desktop : un changement de filtre s'applique tout de suite (le bouton « Appliquer » est masqué par la feuille de style).
		if ( form ) {
			form.addEventListener( 'change', function ( event ) {
				if ( desktop.matches && event.target.type !== 'range' ) {
					form.requestSubmit();
				}
			} );
		}

		// Curseur de prix : deux curseurs qui ne se croisent pas, valeurs affichées, envoi au relâchement.
		var range = filters.querySelector( '[data-ev-range]' );

		if ( range && form ) {
			var lo = range.querySelector( 'input[name="min_price"]' );
			var hi = range.querySelector( 'input[name="max_price"]' );
			var loText = filters.querySelector( '[data-ev-range-lo]' );
			var hiText = filters.querySelector( '[data-ev-range-hi]' );
			var format = range.getAttribute( 'data-format' ) || '{n}';
			var min = parseFloat( lo.min );
			var span = parseFloat( lo.max ) - min;

			var paint = function () {
				range.style.setProperty( '--lo', ( ( lo.value - min ) / span * 100 ) + '%' );
				range.style.setProperty( '--hi', ( ( hi.value - min ) / span * 100 ) + '%' );
				loText.textContent = format.replace( '{n}', lo.value );
				hiText.textContent = format.replace( '{n}', hi.value );
			};

			var onInput = function ( event ) {
				if ( parseFloat( lo.value ) > parseFloat( hi.value ) ) {
					// Le curseur qu'on déplace bute contre l'autre.
					if ( event.target === lo ) {
						lo.value = hi.value;
					} else {
						hi.value = lo.value;
					}
				}

				paint();
			};

			lo.addEventListener( 'input', onInput );
			hi.addEventListener( 'input', onInput );

			// « change » d'un curseur natif : au relâchement de la souris ou du doigt, et à chaque flèche du clavier.
			var rangeTimer = null;
			var onRelease = function () {
				if ( ! desktop.matches ) {
					return;
				}

				window.clearTimeout( rangeTimer );
				rangeTimer = window.setTimeout( function () {
					form.requestSubmit();
				}, 400 );
			};

			lo.addEventListener( 'change', onRelease );
			hi.addEventListener( 'change', onRelease );
		}
	}

	if ( ! header || ! burger ) {
		return;
	}

	function setOpen( open ) {
		header.classList.toggle( 'is-menu-open', open );
		burger.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
	}

	burger.addEventListener( 'click', function () {
		setOpen( burger.getAttribute( 'aria-expanded' ) !== 'true' );
	} );

	// Échap ferme le menu et rend le focus au bouton.
	document.addEventListener( 'keydown', function ( event ) {
		if ( event.key === 'Escape' && burger.getAttribute( 'aria-expanded' ) === 'true' ) {
			setOpen( false );
			burger.focus();
		}
	} );

	// Passer en desktop referme le tiroir : la barre de navigation prend le relais.
	window.matchMedia( '(min-width: 900px)' ).addEventListener( 'change', function ( query ) {
		if ( query.matches ) {
			setOpen( false );
		}
	} );
}() );

/**
 * Pages d'information (Packs, FAQ, CGV).
 *
 * Deux comportements, tous deux facultatifs : une seule question ouverte à la
 * fois dans un accordéon, et le filtre par pastilles. Sans JavaScript, les
 * `<details>` s'ouvrent quand même et toutes les entrées restent visibles.
 */
( function () {
	'use strict';

	// Accordéon : ouvrir une question referme les autres du même groupe.
	document.addEventListener( 'toggle', function ( event ) {
		var item = event.target;

		if ( ! item.open || ! item.matches || ! item.matches( 'details.ev-acc' ) ) {
			return;
		}

		var group = item.closest( '[data-ev-accordion]' );

		if ( ! group ) {
			return;
		}

		// En desktop, les CGV sont des cartes dépliées : on ne les referme pas.
		if ( item.classList.contains( 'ev-cgv__item' ) && window.matchMedia( '(min-width: 900px)' ).matches ) {
			return;
		}

		group.querySelectorAll( 'details.ev-acc[open]' ).forEach( function ( other ) {
			if ( other !== item ) {
				other.open = false;
			}
		} );
	}, true );

	// CGV : cartes dépliées en desktop, accordéon en mobile. Le repli est fait
	// par le script, pas par le gabarit : sans JavaScript, tout reste lisible.
	var wide = window.matchMedia( '(min-width: 900px)' );

	function foldArticles() {
		var items = document.querySelectorAll( 'details.ev-cgv__item' );

		if ( ! items.length || wide.matches ) {
			return;
		}

		items.forEach( function ( item, index ) {
			item.open = index === 0;
		} );
	}

	foldArticles();
	wide.addEventListener( 'change', foldArticles );

	// Pastilles de filtre : masque les entrées qui ne portent pas la valeur choisie.
	document.addEventListener( 'click', function ( event ) {
		var chip = event.target.closest ? event.target.closest( '[data-ev-filter] .ev-chip' ) : null;

		if ( ! chip ) {
			return;
		}

		var bar   = chip.closest( '[data-ev-filter]' );
		// Les entrées filtrées ne sont pas forcément voisines des pastilles : on
		// remonte au bloc qui porte les deux.
		var scope = bar.closest( '[data-ev-filter-scope]' ) || bar.parentNode;
		var value = chip.getAttribute( 'data-ev-filter-value' );

		bar.querySelectorAll( '.ev-chip' ).forEach( function ( other ) {
			var active = other === chip;
			other.classList.toggle( 'is-active', active );
			other.setAttribute( 'aria-pressed', active ? 'true' : 'false' );
		} );

		var shown = 0;

		scope.querySelectorAll( '[data-ev-filter-item]' ).forEach( function ( item ) {
			var values = ( item.getAttribute( 'data-ev-filter-item' ) || '' ).split( ' ' );
			var hidden = value !== 'all' && values.indexOf( value ) === -1;
			item.classList.toggle( 'is-hidden', hidden );

			if ( ! hidden ) {
				shown++;
			}
		} );

		// Compteur « N packs », s'il y en a un à côté des pastilles.
		var count = scope.querySelector( '[data-ev-count-one]' );

		if ( count ) {
			var model = shown > 1 ? count.getAttribute( 'data-ev-count-many' ) : count.getAttribute( 'data-ev-count-one' );
			count.textContent = model.replace( '%d', shown );
		}
	} );

	/*
	 * ── Galerie produit : la rendre utilisable au clavier ───────────────────
	 *
	 * La galerie est le carrousel de WooCommerce (flexslider). Il fabrique ses
	 * vignettes en `<img>` nues, qu'aucune tabulation n'atteint, et laisse
	 * toutes les diapositives focusables même quand elles sont hors écran.
	 * Résultat relevé à l'audit (A11Y-EX-01 et EX-02) : sur un produit à dix
	 * photos, neuf étaient inatteignables sans souris, et la tabulation faisait
	 * défiler la photo sans que flexslider le sache.
	 *
	 * Ce code ne remplace pas le carrousel, il l'habille : chaque vignette
	 * reçoit un vrai `<button>` autour de son image, et le clic est relayé à
	 * l'image — c'est elle que flexslider écoute. Sans JavaScript, il ne se
	 * passe rien de plus qu'avant.
	 *
	 * Les vignettes partagent UN seul arrêt de tabulation, et les flèches
	 * circulent de l'une à l'autre (« roving tabindex ») : dix arrêts avant
	 * d'atteindre le prix feraient de la fiche produit un couloir.
	 */
	/**
	 * Un libellé traduit, posé par `inc/setup.php`, ou son repli français.
	 *
	 * Le repli n'est pas de la décoration : si le script part avant la
	 * déclaration — ordre de chargement, cache agressif —, une vignette sans
	 * nom accessible serait annoncée « bouton », et le clavier redeviendrait
	 * inutilisable pour la raison même qu'on corrige ici.
	 *
	 * @param {string} key      Clé du libellé.
	 * @param {string} fallback Texte de repli.
	 * @return {string} Le libellé à poser.
	 */
	function labels( key, fallback ) {
		return ( window.evasionsGallery && window.evasionsGallery[ key ] ) || fallback;
	}

	var gallery = document.querySelector( '.woocommerce-product-gallery' );

	if ( gallery ) {
		var thumbsReady = false;

		/**
		 * Rend une vignette atteignable : un bouton autour de son image.
		 *
		 * @param {HTMLImageElement} image  Vignette fabriquée par flexslider.
		 * @param {number}           index  Rang de la vignette, à partir de 0.
		 * @param {number}           total  Nombre de vignettes.
		 * @return {HTMLButtonElement} Le bouton créé.
		 */
		function wrapThumb( image, index, total ) {
			var button = document.createElement( 'button' );

			button.type = 'button';
			button.className = 'ev-thumb';
			button.tabIndex = -1;
			/* translators: 1: rang de l'image, 2: nombre d'images. */
			button.setAttribute( 'aria-label', labels( 'thumb', 'Image %1$s sur %2$s' ).replace( '%1$s', index + 1 ).replace( '%2$s', total ) );

			image.parentNode.insertBefore( button, image );
			button.appendChild( image );

			/*
			 * Le clic est renvoyé à l'image : flexslider a posé son écouteur
			 * sur elle, et le déplacer dans le bouton ne l'a pas défait.
			 */
			button.addEventListener( 'click', function () {
				image.click();
			} );

			return button;
		}

		/**
		 * Habille les vignettes et met en place la navigation aux flèches.
		 *
		 * @return {void}
		 */
		function enhanceThumbs() {
			var nav = gallery.querySelector( '.flex-control-thumbs' );

			if ( thumbsReady || ! nav ) {
				return;
			}

			var images = nav.querySelectorAll( 'li > img' );

			if ( ! images.length ) {
				return;
			}

			thumbsReady = true;
			nav.setAttribute( 'aria-label', labels( 'gallery', 'Images du produit' ) );

			var buttons = [];

			Array.prototype.forEach.call( images, function ( image, index ) {
				buttons.push( wrapThumb( image, index, images.length ) );
			} );

			/**
			 * Un seul arrêt de tabulation : celui de la vignette affichée.
			 *
			 * @return {void}
			 */
			function syncThumbs() {
				var active = 0;

				buttons.forEach( function ( button, index ) {
					if ( button.firstChild && button.firstChild.classList && button.firstChild.classList.contains( 'flex-active' ) ) {
						active = index;
					}
				} );

				buttons.forEach( function ( button, index ) {
					button.tabIndex = index === active ? 0 : -1;
					// La coche d'état est sur le bouton, pas sur l'image : c'est lui que le lecteur d'écran annonce.
					button.setAttribute( 'aria-current', index === active ? 'true' : 'false' );
				} );
			}

			nav.addEventListener( 'keydown', function ( event ) {
				var current = buttons.indexOf( document.activeElement );

				if ( current === -1 ) {
					return;
				}

				var next = null;

				if ( 'ArrowRight' === event.key || 'ArrowDown' === event.key ) {
					next = ( current + 1 ) % buttons.length;
				} else if ( 'ArrowLeft' === event.key || 'ArrowUp' === event.key ) {
					next = ( current - 1 + buttons.length ) % buttons.length;
				} else if ( 'Home' === event.key ) {
					next = 0;
				} else if ( 'End' === event.key ) {
					next = buttons.length - 1;
				}

				if ( null === next ) {
					return;
				}

				// La flèche déplace le focus dans la liste, elle ne fait pas défiler la page.
				event.preventDefault();
				buttons[ next ].focus();
				buttons[ next ].click();
			} );

			/*
			 * flexslider change la classe `flex-active` sans prévenir : on la
			 * surveille plutôt que de tenir un compteur en double, qui finirait
			 * par mentir dès qu'une autre commande changerait d'image.
			 */
			new MutationObserver( syncThumbs ).observe( nav, { attributes: true, attributeFilter: [ 'class' ], subtree: true } );
			syncThumbs();
		}

		/**
		 * Seule la diapositive visible garde un lien focusable.
		 *
		 * Sans cela, tabuler dans la galerie emmenait le focus sur une photo
		 * située des milliers de pixels plus loin : le navigateur faisait
		 * défiler la piste pour la montrer, et flexslider, qui ne l'avait pas
		 * demandé, continuait de croire afficher la première (A11Y-EX-02).
		 *
		 * @return {void}
		 */
		function syncSlides() {
			var slides = gallery.querySelectorAll( '.woocommerce-product-gallery__image' );

			Array.prototype.forEach.call( slides, function ( slide ) {
				var link = slide.querySelector( 'a' );

				if ( link ) {
					link.tabIndex = slide.classList.contains( 'flex-active-slide' ) || slides.length === 1 ? 0 : -1;
				}
			} );
		}

		/*
		 * Le carrousel est monté par WooCommerce après ce script. On attend
		 * qu'il ait posé ses vignettes, plutôt que de deviner un délai.
		 */
		var galleryWatcher = new MutationObserver( function () {
			enhanceThumbs();
			syncSlides();
		} );

		galleryWatcher.observe( gallery, { childList: true, subtree: true, attributes: true, attributeFilter: [ 'class' ] } );
		enhanceThumbs();
		syncSlides();
	}

	/*
	 * ── Descriptions repliées derrière « Lire la suite » ────────────────────
	 *
	 * Une description de plusieurs milliers de caractères pousse le formulaire
	 * de commande, les avis et tout le reste hors de l'écran. Le texte est donc
	 * replié à une hauteur de lecture, avec un bouton pour l'ouvrir.
	 *
	 * Le pli n'est posé QUE si le texte dépasse vraiment : un bouton « Lire la
	 * suite » sous trois lignes entières serait un mensonge, et un clic de plus
	 * pour rien. La hauteur vient du CSS (`--ev-readmore-max`), pour qu'elle
	 * puisse changer selon l'écran sans toucher à ce script.
	 */
	document.querySelectorAll( '[data-ev-readmore]' ).forEach( function ( block, index ) {
		/*
		 * On plie D'ABORD, puis on mesure. Mesurer avant, c'était comparer la
		 * hauteur du bloc à elle-même : sans limite posée, `clientHeight` vaut
		 * toujours `scrollHeight`, la condition n'était jamais vraie et aucun
		 * bouton n'apparaissait. Si le texte tient, le pli est retiré et rien
		 * n'aura clignoté — la classe est posée et reprise dans la même image.
		 *
		 * Marge de tolérance : quelques pixels de dépassement, c'est une ligne
		 * orpheline, et ça ne vaut pas un bouton.
		 */
		block.classList.add( 'is-clamped' );

		if ( block.scrollHeight <= block.clientHeight + 24 ) {
			block.classList.remove( 'is-clamped' );
			return;
		}

		var more = block.getAttribute( 'data-ev-readmore-more' ) || 'Lire la suite';
		var less = block.getAttribute( 'data-ev-readmore-less' ) || 'Réduire';

		if ( ! block.id ) {
			block.id = 'ev-readmore-' + index;
		}

		var toggle = document.createElement( 'button' );

		toggle.type = 'button';
		toggle.className = 'ev-readmore';
		toggle.textContent = more;
		// `aria-expanded` et `aria-controls` : le bouton dit ce qu'il ouvre, et dans quel état il est.
		toggle.setAttribute( 'aria-expanded', 'false' );
		toggle.setAttribute( 'aria-controls', block.id );

		toggle.addEventListener( 'click', function () {
			var open = block.classList.toggle( 'is-open' );

			toggle.textContent = open ? less : more;
			toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );

			/*
			 * En refermant, le haut du bloc peut se retrouver au-dessus de
			 * l'écran : le lecteur perdrait le fil de ce qu'il vient de lire.
			 */
			if ( ! open && block.getBoundingClientRect().top < 0 ) {
				block.scrollIntoView( { block: 'start', behavior: still() ? 'auto' : 'smooth' } );
			}
		} );

		block.parentNode.insertBefore( toggle, block.nextSibling );
	} );

	/**
	 * Le visiteur demande-t-il moins de mouvement ?
	 *
	 * @return {boolean} Vrai s'il faut éviter les défilements animés.
	 */
	function still() {
		return window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	}
}() );
