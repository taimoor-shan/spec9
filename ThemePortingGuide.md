# SPEC9 → WordPress Theme: Intern Brief (Carbon Fields)

## 0. What's in the repo

- **Use `public/` as the source of truth.** It's the newer, deployed copy, with the dark-mode logo swap and newer icons. The root `index.html` and `assets/` are an older duplicate.
- **Ignore the deploy scaffolding:** `.vercel`, `.wrangler`, `wrangler.toml`, `src/worker.js`, `.kilo`, `.git` and `__MACOSX`.
- **The site is a single page** with these sections:
  - Header, Hero, Industries, About, Services and Projects
  - Stats, Testimonials and CTA
  - Footer, plus a quote modal
- **The design tokens are already clean.** `themes.css` handles light, dark and auto-dark modes and is the base of the design system.
- **The images are 1.5–3 MB JPEGs**, some with typo filenames (`flloring_*`). Compress and rename them before you upload them to the Media Library.

## 1. Use `front-page.php`, not `home.php`

In WordPress, `home.php` is the blog posts index. It has no page featured image or `the_content()` to pull from.

The setup works like this:

- Create a page called "Home" and set it under Settings → Reading → static front page.
- Build `front-page.php` to render it.
- Keep `index.php` as the required minimal fallback.

## 2. Theme structure

```
spec9/
├─ style.css            (theme header only)
├─ theme.json           (palette, fonts, sizes, layout = design system for editor)
├─ composer.json        (htmlburger/carbon-fields)
├─ vendor/              (composer install --no-dev, shipped with the theme)
├─ functions.php        (loads vendor/autoload.php, boots Carbon Fields, requires inc/*)
├─ inc/ setup.php · enqueue.php · cpt.php · helpers.php
├─ inc/fields/ front-page.php · site-settings.php
├─ assets/ css/tokens.css · css/main.css · js/main.js
├─ header.php · footer.php · index.php · page.php · 404.php
├─ front-page.php       (just calls the section partials in order)
├─ single-service.php · single-project.php
└─ template-parts/
   ├─ front/ hero · industries · about · services · projects · stats · testimonials
   └─ cta · quote-modal · card-service · card-project · section-head
```

## 3. Carbon Fields setup

- **Install with Composer:** `composer require htmlburger/carbon-fields`. Ship `vendor/` with the theme so the site has no Composer dependency at deploy time.
- **Boot it** in `after_setup_theme` with `\Carbon_Fields\Carbon_Fields::boot();`.
- **Register all containers** on the `carbon_fields_register_fields` hook, one file per container in `inc/fields/`.
- **Fields are defined in PHP**, so they are version-controlled automatically. There's no JSON sync or database export to manage.
- **Read values with the Carbon Fields getters:**
  - `carbon_get_post_meta( $id, 'about_heading' )` for page fields
  - `carbon_get_theme_option( 'contact_phone' )` for site-wide fields
  - Never call `get_post_meta()` directly, because Carbon Fields stores keys with a `_` prefix and complex fields are serialized.
- **Read once per partial.** Fetch the section's fields at the top of each partial, then render. Don't scatter getter calls through the markup.

## 4. Use WordPress features before adding any field

| Need | Use this (no Carbon Fields) |
|---|---|
| Hero background | Featured image of the Home page |
| Hero H1, intro text and buttons | `the_content()`: Heading (H1), Paragraph and Buttons blocks |
| Header logo (light) | `custom_logo` |
| Header and footer navigation | `wp_nav_menu()` with locations `primary`, `footer` and `legal` |
| Footer blurb | Site tagline (`bloginfo('description')`) |
| Footer "Services" list | Query of the Services CPT |
| Copyright line | `date('Y')` plus the site name |
| Service and Project card image, text and order | Featured image, excerpt and `menu_order` (enable `page-attributes`) |
| Project caption | Post title |
| Single-page body and extra photos | `the_content()` with the core Gallery block |
| Opening the quote modal | Any link to `#quote` (from menus, buttons or content) |

For the modal trigger, change `main.js` to listen for `a[href="#quote"]` instead of `data-modal-open`. Editors can then open the modal from anywhere without a special field. Register Button block styles named `primary` and `ghost`, mapped to `.btn.p` and `.btn.g`.

## 5. Custom post types (`inc/cpt.php`)

| CPT | Slug | Supports | Notes |
|---|---|---|---|
| `service` | `/services/` | title, editor, thumbnail, excerpt, page-attributes | `show_in_rest` on, `has_archive` on |
| `project` | `/projects/` | title, editor, thumbnail, excerpt, page-attributes | same |

Neither CPT needs any custom fields.

## 6. Fields

Carbon Fields has no "group" field, so organise fields in two ways:

- **One tab per section**, using `->add_tab()`.
- **Every field name prefixed with its section**, for example `about_heading` or `stats_items`. The prefix is the namespace, so names never collide and code stays readable.

**Container "Front Page"** (`post_meta`)

Show this container only on the front page with `->where( 'post_id', '=', get_option( 'page_on_front' ) )`.

| Tab | Fields |
|---|---|
| Industries | `industries_label`, `industries_heading`, `industries_items` (complex: `icon` image, `title` text) |
| About | `about_label`, `about_heading`, `about_lead` (textarea), `about_points` (complex: `title`, `text`), **`about_image`** (image) |
| Services | `services_label`, `services_heading`, `services_card_link_text`. The cards loop all Services by `menu_order`. |
| Projects | `projects_label`, `projects_heading`, `projects_featured` (association to `project`, `set_max(5)`, controls the bento grid) |
| Stats | `stats_bg` (image), `stats_items` (complex: `number`, `suffix`, `label`) |
| Testimonials | `testimonials_label`, `testimonials_heading`, `testimonials_items` (complex: `quote` textarea, `cite` text) |

**Container "Site Settings"** (`theme_options`, global and also used on single pages)

| Tab | Fields |
|---|---|
| Brand | `brand_logo_dark` (image, for dark mode and the footer) |
| Contact | `contact_phone`, `contact_email`, `contact_address` |
| CTA | `cta_heading`, `cta_bg` (image), `cta_btn1_text`, `cta_btn1_url`, `cta_btn2_text`, `cta_btn2_url` |
| Quote modal | `quote_label`, `quote_heading`, `quote_lead`, `quote_form_shortcode` (Contact Form 7), `quote_note` |

**Notes specific to Carbon Fields:**

- **There is no Link field.** That's why each CTA button is a text field plus a URL field.
- **Image fields return an attachment ID.** Output them with `wp_get_attachment_image()` so you get `srcset` and lazy loading.
- **Association fields return arrays** of `type`, `subtype` and `id`. Extract the IDs and pass them to `WP_Query` with `post__in` and `orderby => post__in`, which keeps the editor's chosen order.
- **Industry icons:** if the editors want SVG, allow SVG uploads with sanitization (a small plugin such as Safe SVG). Otherwise, use PNG.
- **Front-page container:** if someone changes the static front page in Settings → Reading, the fields follow the new page. Their values stay on the old one and have to be re-entered.

**Rules for every field:**

- Every section must hide cleanly if its fields are empty. Complex fields return an empty array.
- Escape all output:
  - `esc_html` for text
  - `esc_url` for links
  - `wp_kses_post` for content
  - `do_shortcode` only for the form shortcode

## 7. Design system

- **`tokens.css`** is the copied `themes.css`. It stays the runtime source for colours and handles light, dark and auto-dark. Write no hex colours anywhere else.
- **`theme.json`** mirrors the tokens for the editor:
  - Palette: bg, warm, ink, mute, line, red, dk, dkt
  - Fonts: Barlow and Barlow Condensed
  - Font sizes, including the `h2` clamp
  - Spacing: section padding of 112px
  - Layout: `wideSize` 1240px
- **`main.css`** is `styles.css` split by its existing numbered sections into base, buttons, media, header and so on. Keep the current class names in this phase so the output matches the static site exactly. Renaming classes is a separate ticket.
- **Inline background images** (hero, stats and CTA) are output as a CSS variable, `style="--bg-img:url(...)"`, and styled in CSS.
- **Enqueue assets correctly:**
  - Add the anti-flash theme script inline at the very top of `wp_head`.
  - Enqueue `main.js` with `defer`.
  - Remove the `mailto` and fake-submit logic from `main.js`, since CF7 handles submission now.

## 8. Single pages (keep them minimal)

**Service single page:**

- A header band with the featured image and title
- `the_content()`
- The shared CTA partial

**Project single page:** the same layout, with the photos coming from a Gallery block in the content.

Archives use the `index.php` loop with the card partials.

## 9. Build order

1. Scaffold the theme and add Carbon Fields with Composer. Confirm the containers appear in wp-admin.
2. Write `setup.php` (theme supports, menus, image sizes) and `enqueue.php`. Port `header.php` and `footer.php`.
3. Register the CPTs, then write `inc/fields/front-page.php` and `inc/fields/site-settings.php`.
4. Build `front-page.php` one partial at a time, comparing each section against `public/index.html` in the browser.
5. Build the quote modal with CF7, then the single and archive templates.
6. Enter the real content, then QA:
   - Light, dark and auto modes
   - Mobile breakpoints at 960px and 560px
   - Every section empty
   - Theme Check plugin
   - No PHP notices with `WP_DEBUG` on

## Definition of done

- Every piece of visible text and every image on the site can be edited from wp-admin.
- No content is hardcoded in PHP. Only structural section IDs such as `#about` are allowed.
- The theme works on a fresh install with no plugins except CF7, because Carbon Fields ships inside `vendor/`.
- The front end matches the static site pixel-for-pixel in both themes.

# you asked

message time: 2026-10-01 13:03:21

Cutom logo and standard logo. Global data like contact info fields can go to default customization options.

---

# claude response

Only the sections affected by these changes are below. Everything else in the plan stays the same.

## 2. Theme structure (changes only)

```
├─ inc/ setup.php · enqueue.php · cpt.php · customizer.php · helpers.php
├─ inc/fields/ front-page.php          (site-settings.php is removed)
```

## 3. Carbon Fields setup (changes only)

- **Carbon Fields is now only used for the front page.** It has no `theme_options` container, so `carbon_get_theme_option()` isn't used anywhere.
- **Global data is read from the Customizer** with `get_theme_mod( 'spec9_contact_phone', '' )`.
- **Prefix every Customizer setting with `spec9_`** so it never collides with core or plugin settings.

## 4. Use WordPress features before adding any field (updated rows)

| Need | Use this |
|---|---|
| Standard logo (header, light mode) | Core `custom_logo` (Customizer → Site Identity) |
| Dark-mode logo (header in dark mode, footer) | Customizer setting `spec9_logo_dark`, placed in Site Identity right under Logo |
| Contact info, CTA band, quote modal | Customizer sections (see section 6) |

## 6. Fields

**Front-page fields (Carbon Fields)**

The "Front Page" container is unchanged:

- Tabs for Industries, About, Services, Projects, Stats and Testimonials
- Field names prefixed with their section
- Shown only on the front page with `post_id = page_on_front`

**Global data (Customizer, `inc/customizer.php`)**

Register everything on the `customize_register` hook. Each item is a setting with a `sanitize_callback` plus a control.

| Section | Settings | Control and sanitizer |
|---|---|---|
| Site Identity (core) | `custom_logo` (core), `spec9_logo_dark` | `WP_Customize_Media_Control` (image) with `absint`, priority 9 so it sits next to Logo |
| Contact details | `spec9_contact_phone`, `spec9_contact_email`, `spec9_contact_address` | text with `sanitize_text_field`, email with `sanitize_email`, textarea with `sanitize_textarea_field` |
| Call to action | `spec9_cta_heading`, `spec9_cta_bg`, `spec9_cta_btn1_text`, `spec9_cta_btn1_url`, `spec9_cta_btn2_text`, `spec9_cta_btn2_url` | text, Media control with `absint`, URL with `esc_url_raw` |
| Quote modal | `spec9_quote_label`, `spec9_quote_heading`, `spec9_quote_lead`, `spec9_quote_form_shortcode`, `spec9_quote_note` | text and textarea with `sanitize_text_field` / `sanitize_textarea_field` |

Group the three custom sections under one "SPEC9 Theme" panel.

**Logo rules:**

- **Enable the core logo** with `add_theme_support( 'custom-logo', [ 'flex-width' => true, 'flex-height' => true ] )`.
- **Header markup:** output two images, both from attachment IDs with `wp_get_attachment_image()`:
  - the custom logo with class `header-logo-light`
  - the dark logo with class `header-logo-dark`
  
  The existing CSS in `tokens.css` handles the swap between them.
- **Footer** uses the dark logo.
- **Fallbacks:**
  - If no dark logo is set, use the custom logo everywhere.
  - If neither logo is set, output the site name as text.
- **Alt text** is the site name.

**Contact rules:**

- **Phone link:** build the `tel:` href by stripping everything except digits and `+` from the phone number. Display the number as the editor typed it.
- **Email link:** output the `mailto:` link with `antispambot()`.
- **Empty values:** hide any empty item, in the footer and anywhere else it's used.

**Customizer note:** theme mods are stored per theme. If the theme folder or slug is renamed, these values have to be re-entered.

## 9. Build order (step 3 updated)

3. Register the CPTs, write `inc/fields/front-page.php`, then write `inc/customizer.php` and enter the logos, contact details, CTA and modal content in the Customizer.

## Definition of done (added)

- **Global content lives in Customizer → SPEC9 Theme** (contact details, CTA and quote modal), and the dark logo sits under Site Identity.
- **No duplicate global settings.** No global setting is stored in Carbon Fields or as page meta.
- **Live preview works.** Changes to these settings show in the Customizer preview (a refresh is fine).