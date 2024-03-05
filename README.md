# NM Readability Wordpress content analyser

This is a Wordpress plugin that analyses the content of posts and assigns an estimated minimum reading age for that content. It is based on a variety of algorhymic text tests via the `readability-scores` NPM package.

## Boilerplate Base

Plugin documentation with instalation instruction and best practices can be found at [wiki page](https://github.com/code-soup/wordpress-plugin-boilerplate/wiki).

## Coding Standards

-   `wpcs` : analyze code against the WordPress coding standards with PHP_CodeSniffer.
-   `cbf` : fix coding standards warnings/errors automatically with PHP Code Beautifier.
-   `lint` : lint PHP files against parse errors.

To check a file against the WordPress coding standards or to automatically fix coding standards, simply specify the file's location:

-   `wpcs includes/class-init.php`
-   `cbf includes/class-init.php`

## Final thoughts
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
