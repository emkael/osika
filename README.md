OSiKa
=====

Narzędzie oceny siły rąk brydżowych w oparciu o algorytmy licytacji naturalnej Łukasza Sławińskiego

Wymagania systemowe
-------------------

### Wersja konsolowa:

 * interpreter PHP 5.4+ z modułem JSON

### Wersja interaktywna (WWW), dodatkowo:

 * eee... serwer WWW?
 * biblioteki JavaScript:
   + [jQuery](http://jquery.com)
   + [Mustache](https://github.com/janl/mustache.js/)

Instalacja
----------

### Wersja konsolowa:

Wystarczające jest ściągnięcie [paczki](osika.zip) z głównego katalogu repozytorium albo ręczne ściągnięcie całości katalogu [/bin/](bin/) z repozytorium.

### Wersja interaktywna:

Po ściągnięciu z repozytorium katalogów [/web/](web/) oraz [/bin/](bin/), należy:
 * umieścić zawartość katalogu [/web/](web/) w miejscu dostępnym dla serwera WWW
 * w podkatalogu /web/lib/ umieścić wymagane biblioteki JavaScript (niedostarczane z aplikacją)
 * jeśli potrzeba, w pliku /web/index.html edytować ścieżki do bibliotek JavaScript
 * w pliku [/web/osika.php](web/osika.php) edytować linię rozpoczynającą się od `require_once` tak, aby wskazywała na odpowiedni plik katalogu [/bin/lib/](bin/lib/)

Użycie
------

### Wersja konsolowa:

W katalogu instalacji wydać polecenie:
```
php osika [OPCJE] REKA
```

Dodatkowo, w systemach uniksowych, po nadaniu praw do wykonywania dla pliku [/bin/osika](bin/osika/), możliwe jest bezpośrednie wywołanie:
```
./osika [OPCJE] REKA
```

REKA

Dane wejściowe - zawartość ręki. Wszystkie 13 kart w formacie przecinkowym, tj. xxx,xxx,xxxx,xxx. Dodatkowo:
 * ignorowane są białe znaki.
 * wielkość liter nie ma znaczenia
 * ręka musi zawierać 13 kart
 * x oznacza dowolną blotkę (ale cyfry również dozwolone)
 * Dama = Q/D, Walet = J/W, 10 = 10/T
 * w pojedynczym kolorze nie mogą duplikować się honory ani 9
 * kolejność kart w kolorze nie ma znaczenia
 * blotki nie są weryfikowane (kolor może posiadać zduplikowane blotki, może też posiadać niemożliwą liczbę blotek, np. 10)

OPCJE

-h, --help: wyświetlają instrukcję obsługi

-f FORMAT, --format FORMAT: format wyników programu; dostępne wartości: raw, table, json; wartość domyślna: table

-s KOLORY, --suits KOLORY: lista kolorów, dla których podawane są wyniki (rozdzielona przecinkami); dostępne wartości: s, h, d, c, total, all; wartość domyślna: all

-c KATEGORIE, --categories KATEGORIE: lista składników analizy siły ręki (rozdzielona przecinkami); dostępne wartości wymienione są w pomocy programu (`php osika -h`); wartość domyślna: all

### Wersja interaktywna:

Się wpisuje, się klika i się wyświetla.

Podziękowania i autorstwo
-------------------------

Autorem algorytmów licytacji naturalnej (w tym algorytmu oceny siły karty OSiKa) jest Łukasz Sławiński (Pikier).

Program powstał z pomysłu i przy aktywnym współudziale użytkowników [forumbridge.pl](http://www.forumbridge.pl), w szczególności:
 * Tomasza Radko (TRad)
 * Marka Walczaka (walec)

Autorem kodu źródłowego jest Michał Klichowicz (mkl).

Licencja
--------

Program udostępniany jest na licencji GPL wersji 2.

Szczegóły licencji znajdują się w pliku [LICENSE](LICENSE)

***
`She said, do me a favour, and stop flattering yourself.`
