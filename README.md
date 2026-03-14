# NM Readability Analyser

WordPress plugin that analyses post content readability and estimates read time.

## What it does

- Calculates reading age using 6 formulas (Dale-Chall, ARI, Coleman-Liau, Flesch-Kincaid, Gunning Fog, SMOG)
- Estimates read time based on word count (238 wpm average)
- Displays results in a metabox in the post editor
- Saves results as post meta: `nm_readability_age` and `nm_read_time`
- Exposes both fields via the WordPress REST API

## REST API

Both meta fields are available on the `posts` endpoint:

```
GET /wp-json/wp/v2/posts/123

{
  "meta": {
    "nm_readability_age": 14,
    "nm_read_time": 5
  }
}
```

## Development

```bash
npm install
npm run watch    # development with auto-rebuild
npm run build    # production build (minified)
```

## Deployment

Deployed via WP Pusher from this repo. The `dist/` directory is committed.

## Final thoughts
```
 _    _  ____  ____  _   _       ___  _____  __  __  __  __  __  __  _  _  ____  ___  __  __
( \/\/ )(_  _)(_  _)( )_( )     / __)(  _  )(  \/  )(  \/  )(  )(  )( \( )(_  _)/ __)(  \/  )
 )    (  _)(_   )(   ) _ (     ( (__  )(_)(  )    (  )    (  )(__)(  )  (  _)(_ \__ \ )    (
(__/\__)(____) (__) (_) (_)     \___)(_____)(_/\/\_)(_/\/\_)(______)(_)\_)(____)(___/(_/\/\_)

 _    _  ____      _    _  ____  __    __        ____  ____        __    ____  __    ____
( \/\/ )( ___)    ( \/\/ )(_  _)(  )  (  )      (  - \( ___)      /__\  (  - \(  )  ( ___)
 )    (  )__)      )    (  _)(_  )(__  )(__      ) _ < )__)      /(  )\  ) _ < )(__  )__)
(__/\__)(____)    (__/\__)(____)(____)(____)    (____/(____)    (__)(__)(____/(____)(____)

 ____  _____      ___  ____  ____    __    _  _      _    _  ____  ____  _   _        __    __    ____  ____  _  _  ___
(_  _)(  _  )    / __)(  _ \( ___)  /__\  ( )/ )    ( \/\/ )(_  _)(_  _)( )_( )      /__\  (  )  (_  _)( ___)( \( )/ __)
  )(   )(_)(     \__ \ )___/ )__)  /(  )\  )  (      )    (  _)(_   )(   ) _ (      /(  )\  )(__  _)(_  )__)  )  ( \__ \
 (__) (_____)    (___/(__)  (____)(__)(__)(_)\_)    (__/\__)(____) (__) (_) (_)    (__)(__)(____)(____)(____)(_)\_)(___/

 ____  _____  ____  _   _      _  _  ____    __    ____        __    _  _  ____       ____    __    ____
(  - \(  _  )(_  _)( )_( )    ( \( )( ___)  /__\  (  _ \      /__\  ( \( )(  _ \     ( ___)  /__\  (  _ \
 ) _ < )(_)(   )(   ) _ (      )  (  )__)  /(  )\  )   /     /(  )\  )  (  )(_) )     )__)  /(  )\  )   /
(____/(_____) (__) (_) (_)    (_)\_)(____)(__)(__)(_)\_)    (__)(__)(_)\_)(____/     (_)   (__)(__)(_)\_)
```
