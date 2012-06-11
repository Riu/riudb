riudb
=====

Mechanizm składowania danych dla systemów zarządzania treścią. 

## Wstęp

RiuDB to koncept zarządzania i archwizowania informacji za pomocą plików JSON i relacyjnych baz danych. Bezpośrednią inspiracją do jego stworzenia były porównania możliwości relacyjnych i nierelacyjnych baz danych w kontekście tworzenia aplikacji społecznościowych takich jak Facebook, czy G+. 

## Opis

RiuDB działa w oparciu o koncepcje unikalnych identfikatorów - podobnie jak w MongoDB każdy rekord zapisywany do bazy MySQL otrzymuję unikalny id. Oprócz tego w bazie przetrzymywane jest informacja (moduł) definiowana przez programistę - czym ma być dany rekord np: tagiem, wpisem na forum, użytkownikiem. (o powstawaniu koncepcji można przeczytać tutaj - http://forum.kohanaphp.pl/index.php/topic,961.0.html )

Przykładowa tabela danych:

	CREATE TABLE IF NOT EXISTS `records` (
	  `id` int(10) unsigned NOT NULL auto_increment,
	  `module` tinyint(3) unsigned NOT NULL,
	  PRIMARY KEY  (`id`),
	  KEY `module` (`module`)
	) ENGINE=InnoDB AUTO_INCREMENT=1 ;

Każda następna tabela w bazie powinna zawierać powiązanie do id i określone dane, które chcemy posiadać w danym module. Przykładowa tabela tagów:

	CREATE TABLE IF NOT EXISTS `tags` (
	  `id` int(10) unsigned NOT NULL auto_increment,
	  `record` int(10) unsigned NOT NULL,
	  `slug` varchar(128) NOT NULL,
	  PRIMARY KEY  (`id`),
	  KEY `record` (`record`)
	) ENGINE=InnoDB AUTO_INCREMENT=1 ;
	ALTER TABLE `tags`
	  ADD CONSTRAINT `tags_ibfk_1` FOREIGN KEY (`record`) REFERENCES `records` (`id`) ON DELETE CASCADE;

W momencie gdy dodajemy za pomocą insert nowy rekord do `records` zwracany jest jego id i dopiero wtedy dodajemy tag gdzie zwrócone records.id=tags.record. Analogicznie możemy w ten sam sposób dodawać posty na blogu, użytkowników, kategorie - zawsze elementem łączącym będzie `record`. Między poszczególnymi rekordami oczywiście będą występować relacje np.: post będzie miał wiele tagów i będzie stworzony przez jakiegoś użytkownika. 

Ideą RiuDB jest tworzenie dokumentu zawierającego dane z danego modułu podobnie jak w mongo lecz z wykorzystaniem relacyjności baz danych:

- zapis danych do bazy mysql
- zapis danych z mysql do json
- odczyt tylko relacji między rekordami z wykorzystaniem mysql
- odczyt właściwych danych bez połączenia z bazą danych

W koncpecji tej MySQL służy tylko do pobrania określonej warunkami grupy identyfikatorów, na podstawie których pobrane zostaną informacje z dokumentów przypisanych do tych identyfikatorów. Relacje te zapisujemy również w samych dokumentach, co powoduje, że w do odczytania dokumentu o określonym id nie potrzebujemy łączyć się bazą - nie musimy również łączyć się bazą aby odczytać z jakimi innymi dokumentami jest on powiązany. Ewentualne zapytania z użyciem `JOIN` w bazie MySQL operują jedynie na danych typu `int` z wykorzystniem indeksów.

Każdy dokument magazynowany jest w strukturze `modul/XX/XX/XX/XX/id.json` gdzie z `id` tworzony jest liczba traktowana jest jako `string` po którego rozbiciu otrzymujemy foldery będące ścieżką do pliku dokumentu. W ścieżce tej możemy również tworzyć foldery o określnym `id` w którym przetrzymujemy dane związane z dokumentem. Domyślnie moduł to `records`, lecz można mieć wiele tabel takich jak `records`.

## Przykłady

$id - pojedynczy dokument (int)
$array - tablica złożona z `id` dokmentów
$data - dane zapisywane do dokumentu (array)

- dodawanie pliku dokumentu $id, bez folderu, plik bez danych, dane domyślnie zakodowane w jsonie

	Riudb::factory()->id($id)->add();

- dodawanie pliku dokumentu $id, z folderem $id, plik z danymi niezakodowany w json

	Riudb::factory()->id($id)->add(TRUE, 'coś');

- dodawanie pliku poddokumentu do folderu głównego,plik z danymi niezakodowany w json

	Riudb::factory()->id($id)->addfile('plik','tekst');

- dodawanie pliku poddokumentu do folderu głównego,plik z danymi niezakodowany w json, dodanie podfolderu do folderu dokumentu

	Riudb::factory()->id($id)->addfile('plik','tekst')->addfolder('mini');

- dodawanie pliku dokumentu z folderem, poddokumentem, podfolderami

	Riudb::factory()->id($id)->add(TRUE)->addfile('plik','tekst')->addfolder('mini')->addfolder('medium');

- usunięcie pliku dokumentu

	Riudb::factory()->id($id)->delfile();

- usunięcie pliku poddokumentu

	Riudb::factory()->id($id)->delfile('plik');

- usunięcie podfolderu folderu głównego dokumentu

	Riudb::factory()->id($id)->deldir('mini');

- usunięcie folderu głównego dokumentu

	Riudb::factory()->id($id)->deldir();

- dodawanie pliku dokumentu 1, z folderem 1, plik z danymi niezakodowany w json dla modułu `users`

	Riudb::factory('users')->id($id)->add(TRUE);

- pobranie ścieżki do danego dokumentu

	Riudb::factory()->id($id)->getpath();

-pobranie dokumentu id z dołączeniem do niego dokumentu określonego w wartości `parent`

	Riudb::factory()->get($id)->join('parent')->render();

- pobranie dokumentów o użytkownikach posiadających id z tablicy $array

	Riudb::factory('users')->get($array)->render();

- zapisywanie tablicy danych do dokumentu

	Riudb::factory()->id($id)->save($data);

- pobranie: dokumentu o identyfikatorze $id, dokumentu o identyfikatorze $parent dla $id, dokumentu o identyfikatorze $user dla $id, pobranie dwóch komentarzy wraz ich autorami i odwróceniem ich w odwrotnej kolejności.

	Riudb::factory()->get($id)->join('parent')->join(array('user','users'))->join('comments',array('user','users'),2,TRUE)->render();

## Zastosowanie

Klony Facebooka/Google+.

## ToDo

- zapsywanie nowych "kolumn" do dokumentów
- zagnieżdżone relacje dla więcej niż jednego dokumentu.
