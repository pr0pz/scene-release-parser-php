<?php
namespace ReleaseParser;
require_once( 'ReleasePatterns.php' );

/**
 * ReleaseParser - A tool for parsing scene release names.
 *
 * @package ReleaseParser
 * @author Wellington Estevo
 * @version 1.0.1
 */

class ReleaseParser extends ReleasePatterns {

	/** @var string Original rls name. */
	private $release = '';
	/** @var mixed[] Release information vars. */
	public $data = [
		'release'		=> \null, // Original rls name
		'title'			=> \null, // First part of title
		'titleExtra'	=> \null, // Second part of title (optional) like Name of track/book/xxx etc.
		'group'			=> \null,
		'year'			=> \null,
		'date'			=> \null,
		'season'		=> \null, // For TV rls
		'episode'		=> \null, // For TV/Audiobook/Ebook (issue) rls
		'flags'			=> \null, // Misc rls name flags
		'source'		=> \null,
		'format'		=> \null, // Rls format/encoding
		'resolution'	=> \null, // For Video rls
		'audio'			=> \null, // For Video rls
		'device'		=> \null, // For Software/Game rls
		'os'			=> \null, // For Software/Game rls
		'version'		=> \null, // For Software/Game rls
		'language'		=> \null, // Array with language code as key and name as value (in english)
		'type'			=> \null,
	];


	/**
	 * ReleaseParser Class constructor.
	 * 
	 * The order of the parsing functions DO matter.
	 *
	 * @param string $releaseName Original release name
	 * @param string $section Release section
	 * @return void 
	 */
	public function __construct( string $releaseName, string $section = '' )
	{
		// Save orignal release name.
		$this->release = $releaseName;
		$this->set( 'release', $this->release );

		// Parse everything.
		// The parsing order DO matter!
		$this->parseGroup();
		$this->parseFlags();		// Misc rls name flags
		$this->parseOs();			// For Software/Game rls: Operating System
		$this->parseDevice();		// For Software/Game rls: Device (like console)
		$this->parseVersion();		// For Software/Game rls: Version
		$this->parseEpisode();		// For TV/Audiobook/Ebook (issue) rls: Episode
		$this->parseSeason();		// For TV rls: Season
		$this->parseDate();
		$this->parseYear();
		$this->parseFormat();		// Rls format/encoding
		$this->parseSource();
		$this->parseResolution();	// For Video rls: Resolution (720, 1080p...)
		$this->parseAudio();		// For Video rls: Audio format
		$this->parseLanguage();		// Array with language code as key and name as value (in english)
		$this->parseSource();		// Source (2nd time, for right web source)
		$this->parseType( $section );
		$this->parseTitle();		// Title and extra title
	}

	/**
	 * The __toString() method allows a class to decide how it will react when it is treated like a string.
	 * 
	 * https://www.php.net/manual/en/language.oop5.magic.php#object.tostring
	 *
	 * @return string $classToString Stringified attribute values.
	 */
	public function __toString(): string
	{
		$classToString = '';
		$type = \strtolower( $this->get( 'type' ) );

		// Loop all values and put together the stringified class
		foreach( $this->get( 'all' ) as $information => $informationValue ) {

			// Skip original release name and debug
			if ( $information === 'release' || $information === 'debug' ) continue;

			// Rename var title based on attributes
			if ( !empty( $this->get( 'titleExtra' ) ) ) {
				if ( $information === 'title' ) {
					if ( $type === 'ebook' || $type === 'abook' ) {
						$information = 'Author';
					} else if ( $type === 'music' || $type === 'musicvideo' ) {
						$information = 'Artist';
					} else if ( $type === 'tv' || $type === 'anime' ) {
						$information = 'Show';
					} else if ( $type === 'xxx' ) {
						$information = 'Publisher';
					} else {
						$information = 'Name';
					}

				// Rename titleExtra based on attributes
				} else if( $information === 'titleExtra' ) {
					if ( $this->hasAttribute( [ 'CD Single', 'VLS' ], 'source' ) ) {
						$information = 'Song';
					} else if ( $this->hasAttribute( [ 'CD Album', 'Vynil', 'LP' ], 'source' ) ) {
						$information = 'Album';
					} else if ( $this->hasAttribute( [ 'EP', 'CD EP' ], 'source' ) ) {
						$information = 'EP';
					} else {
						$information = 'Title';
					}
				}
			}

			// Value set?
			if ( isset( $informationValue ) ) {

				// Some attributes can have more then one value.
				// So put them together in this var.
				$values = '';

				// Date (DateTime) is the only obect,
				// So we have to handle it differently.
				if ( \is_object( $informationValue ) ) {

					$values = $informationValue->format( 'd.m.Y' );

				// Only loop of it's not an object
				} else {

					$values = \is_array( $informationValue ) ? $values . \implode( ', ', $informationValue ) : $informationValue;
				}

				// Separate every information type with a slash
				if ( !empty( $classToString ) ) {
					$classToString .= ' / ';
				}
				$classToString .= \ucfirst( $information ) . ': ' . $values;
			}
		}

		return $classToString;
	}


	/**
	 * This method is called by var_dump().
	 * 
	 * https://www.php.net/manual/en/language.oop5.magic.php#object.debuginfo
	 *
	 * @return mixed[] $informations Removed vars without values.
	 */
	public function __debugInfo()
	{
		return $this->get( 'all' );
	}


	/**
	 * Parse release language/s.
	 *
	 * @return void
	 */
	private function parseLanguage(): void
	{
		$languageCodes = [];

		// Search and replace pattern in regex pattern for better macthing
		$regexPattern = $this->cleanupPattern( $this->release, self::REGEX_LANGUAGE, [ 'audio', 'device', 'flags', 'format', 'group', 'os', 'resolution', 'source', 'year' ] );


		// Loop all languages
		foreach ( self::LANGUAGES as $languageCodeKey => $languageName ) {

			// Turn every var into an array so we can loop it
			if ( !\is_array( $languageName ) ) {
				$languageName = [ $languageName ];
			}

			// Loop all sub language names
			foreach ( $languageName as $name ) {

				// Insert current lang pattern
				$regex = \str_replace( '%language_pattern%', $name, $regexPattern );

				// Check for language tag (exclude "grand" for formula1 rls)
				\preg_match( $regex, $this->release, $matches );

				if ( !empty( $matches ) ) {

					$languageCodes[] = $languageCodeKey;
				}
			}
		}

		if ( !empty( $languageCodes ) ) {

			$languages = [];

			foreach( $languageCodes as $languageCode ) {
				// Get language name by language key
				$language = self::LANGUAGES[ $languageCode ];
				// If it's an array, get the first value as language name
				if ( \is_array( $language ) ) {
					$language = self::LANGUAGES[ $languageCode ][0];
				}

				$languages[ $languageCode ] = $language;
			}

			$this->set( 'language', $languages );
		}
	}


	/**
	 * Parse release date.
	 *
	 * @return void
	 */
	private function parseDate(): void
	{
		// Check for normal date
		\preg_match( '/[._\(-]' . self::REGEX_DATE . '[._\)-]/i', $this->release, $matches );

		$day = $month = $year = $temp = $date = '';

		if ( !empty( $matches ) ) {

			// Date formats: 21.09.16 (default) / 16.09.2021 / 2021.09.16 / 09.16.2021
			$year = (int) $matches[1];
			$month = (int) $matches[2];
			$day = (int) $matches[3];

			// On older Mvid releases the format is year last.
			if ( \preg_match( self::REGEX_DATE_MUSIC, $this->release ) ) {
				$temp = $year;
				$year = $day;
				$day = $temp;
			}

			// 4 digits day (= year) would change the vars.
			if ( \strlen( (string) $day ) == 4 ) {
				$temp = $year;
				$year = $day;
				$day = $temp;
			}

			// Month > 12 means we swap day and month (16.09.2021)
			// What if day and month are <= 12?
			// Then it's not possible to get the right order, so date could be wrong.
			if ( $month > 12 ) {
				$temp = $day;
				$day = $month;
				$month = $temp;
			}

			// 2 digits year has to be converted to 4 digits year
			// https://www.php.net/manual/en/datetime.createfromformat.php (y)
			if ( \strlen( (string) $year ) == 2 ) {
				$yearNew = 0;
				try {
					$yearNew = \DateTime::createFromFormat( 'y', $year );
				} catch ( \Exception $e ) {
					\trigger_error( 'Datetime Error (Year): ' . $year . ' / rls: ' . $this->release );
				}

				// If DateTime was created succesfully, just get the 4 digit year
				if ( !empty( $yearNew ) ) {
					$year = $yearNew->format( 'Y' );
				}
			}

			// Build date string
			$date = $day . '.' . $month . '.' . $year;

			// Try to create datetime object
			// No error handling if it doesn't work.
			try {
				$this->set( 'date', \DateTime::createFromFormat( 'd.m.Y', $date ) );
			} catch ( \Exception $e ) {
				\trigger_error( 'Datetime Error (Date): ' . $date . ' / rls: ' . $this->release );
			}

		} else {

			// Cleanup release name for better matching
			$releaseNameCleaned = $this->cleanup( $this->release, 'episode' );

			// Put all months together
			$allMonths = \implode( '|', self::MONTHS );
			// Set regex pattern
			$regexPattern = \str_replace( '%monthname%', $allMonths, self::REGEX_DATE_MONTHNAME );
			// Match day, month and year
			\preg_match_all( '/[._-]' . $regexPattern . '[._-]/i', $releaseNameCleaned, $matches );

			$lastResultKey = $day = $month = $year = '';

			// If match: get last matched value (should be the right one)
			// Day is optional, year is a must have.
			if ( !empty( $matches[0] ) ) {

				$lastResultKey = array_key_last( $matches[0] );

				// Day, default to 1 if no day found
				$day = 1;
				if ( !empty( $matches[1][ $lastResultKey ] ) ) {
					$day = $matches[1][ $lastResultKey ];
				} else if ( !empty( $matches[3][ $lastResultKey ] ) ) {
					$day = $matches[3][ $lastResultKey ];
				} else if ( !empty( $matches[5][ $lastResultKey ] ) ) {
					$day = $matches[5][ $lastResultKey ];
				}

				// Month
				$month = $matches[2][ $lastResultKey ];

				// Year
				$year = $matches[4][ $lastResultKey ];

				// Check for month name to get right month number
				foreach ( self::MONTHS as $monthNumber => $monthPattern ) {

					\preg_match( '/' . $monthPattern . '/i', $month, $matches );

					if ( !empty( $matches ) ) {
						$month = $monthNumber;
						break;
					}
				}

				// Build date string
				$date = $day . '.' . $month . '.' . $year;

				// Try to create datetime object
				// No error handling if it doesn't work.
				try {
					$this->set( 'date', \DateTime::createFromFormat( 'd.m.Y', $date ) );
				} catch ( \Exception $e ) {
					\trigger_error( 'Datetime Error (Date): ' . $date . ' / rls: ' . $this->release );
				}
			}
		}
	}


	/**
	 * Parse release year.
	 *
	 * @return void
	 */
	private function parseYear(): void
	{
		// Remove any version so regex works better (remove unneeded digits)
		$releaseNameCleaned = $this->cleanup( $this->release, 'version' );

		// Match year
		\preg_match_all( self::REGEX_YEAR, $releaseNameCleaned, $matches );

		if ( !empty( $matches[1] ) ) {

			// If we have any matches, take the last possible value (normally the real year).
			// Release name could have more than one 4 digit number that matches the regex.
			// The first number would belong to the title.
			// Sanitize year if it's not only numeric ("199X"/"200X")
			$year = \end( $matches[1] );
			$year = \is_numeric( $year ) ? (int) $year : $this->sanitize( $year );
			$this->set( 'year', $year );

		// No Matches? Get year from parsed Date instead.
		} else if ( !empty( $this->get( 'date' ) ) ) {
			$this->set( 'year', $this->get( 'date' )->format( 'Y' ) );
		}
	}


	/**
	 * Parse release device.
	 *
	 * @return void
	 */
	private function parseDevice(): void
	{
		$device = '';

		// Cleanup release name for better matching
		$releaseNameCleaned = $this->cleanup( $this->release, [ 'flags', 'os' ] );

		// Loop all device patterns
		foreach ( self::DEVICE as $deviceName => $devicePattern ) {

			// Turn every var into an array so we can loop it
			if ( !\is_array( $devicePattern ) ) {
				$devicePattern = [ $devicePattern ];
			}

			// Loop all sub patterns
			foreach ( $devicePattern as $pattern ) {

				// Match device
				\preg_match( '/[._-]' . $pattern . '-\w+$/i', $releaseNameCleaned, $matches );

				// Match found, set type parent key as type
				if ( !empty( $matches ) ) {
					$device = $deviceName;
					break;
				}
			}
		}

		if ( !empty( $device ) ) {
			$this->set( 'device', $device );
		}
	}


	/**
	 * Parse release flags.
	 *
	 * @return void
	 */
	private function parseFlags(): void
	{
		$flags = $this->parseAttribute( self::FLAGS );

		if ( !empty( $flags ) ) {
			// Always save flags as array
			$flags = !\is_array( $flags ) ? [ $flags ] : $flags;
			$this->set( 'flags', $flags );
		}
	}


	/**
	 * Parse the release group.
	 *
	 * @return void
	 */
	private function parseGroup(): void
	{
		\preg_match( self::REGEX_GROUP, $this->release, $matches );

		if ( !empty( $matches[1] ) ) {
			$this->set( 'group', $matches[1] );
		} else {
			$this->set( 'group', 'NOGRP' );
		}
	}

	/**
	 * Parse release version (software, games, etc.).
	 *
	 * @return void
	 */
	private function parseVersion(): void
	{
		\preg_match( '/[._-]' . self::REGEX_VERSION . '[._-]/i', $this->release, $matches );
		if ( !empty( $matches ) ) $this->set( 'version', $matches[1] );
	}


	/**
	 * Parse release source.
	 *
	 * @return void
	 */
	private function parseSource(): void
	{
		$source = $this->parseAttribute( self::SOURCE );

		if ( !empty( $source ) ) {
			// Only one source allowed, so get first parsed occurence (should be the right one)
			$source = \is_array( $source ) ? \reset( $source ) : $source;
			$this->set( 'source', $source );
		}
	}


	/**
	 * Parse release format/encoding.
	 *
	 * @return void
	 */
	private function parseFormat(): void
	{
		$format = $this->parseAttribute( self::FORMAT );

		if ( !empty( $format ) ) {
			// Only one source allowed, so get first parsed occurence (should be the right one)
			$format = \is_array( $format ) ? \reset( $format ) : $format;
			$this->set( 'format', $format );
		}
	}


	/**
	 * Parse release resolution.
	 *
	 * @return void
	 */
	private function parseResolution(): void
	{
		$resolution = $this->parseAttribute( self::RESOLUTION );

		if ( !empty( $resolution ) ) {
			// Only one resolution allowed, so get first parsed occurence (should be the right one)
			$resolution = \is_array( $resolution ) ? \reset( $resolution ) : $resolution;
			$this->set( 'resolution', $resolution );
		}
	}


	/**
	 * Parse release audio.
	 *
	 * @return void
	 */
	private function parseAudio(): void
	{
		$audio = $this->parseAttribute( self::AUDIO );
		if ( !empty( $audio ) ) $this->set( 'audio', $audio );
	}


	/**
	 * Parse release operating system.
	 *
	 * @return void
	 */
	private function parseOs(): void
	{
		$os = $this->parseAttribute( self::OS );
		if ( !empty( $os ) ) $this->set( 'os', $os );
	}


	/**
	 * Parse release season.
	 *
	 * @return void
	 */
	private function parseSeason(): void
	{
		\preg_match( self::REGEX_SEASON, $this->release, $matches );

		if ( !empty( $matches ) ) {

			// key 1 = 1st pattern, key 2 = 2nd pattern
			$season = !empty( $matches[1] ) ? $matches[1] : \null;
			$season = empty( $season ) && !empty( $matches[2] ) ? $matches[2] : $season;

			if ( isset( $season ) ) $this->set( 'season', (int) $season );
		}
	}


	/**
	 * Parse release episode.
	 *
	 * @return void
	 */
	private function parseEpisode(): void
	{
		\preg_match( '/[._-]' . self::REGEX_EPISODE . '[._-]/i', $this->release, $matches );

		if ( !empty( $matches ) ) {

			// key 1 = 1st pattern, key 2 = 2nd pattern
			// 0 can be a valid value
			$episode = isset( $matches[1] ) && $matches[1] != '' ? $matches[1] : \null;
			$episode = !isset( $episode ) && isset( $matches[2] ) && $matches[2] != '' ? $matches[2] : $episode;

			if ( isset( $episode ) ) {
				// Sanitize episode if it's not only numeric (eg. more then one episode found "1 - 2")
				if ( \is_numeric( $episode ) && $episode !== '0' ) {
					$episode = (int) $episode;
				} else {
					$episode = $this->sanitize( \str_replace( [ '_', '.' ], '-', $episode ) );
				}
				$this->set( 'episode', $episode );
			}
		}
	}


	/**
	 * Parse the release type by section.
	 *
	 * @param string $section Original release section.
	 * @return void
	 */
	private function parseType( string &$section ): void
	{
		// 1st: guesss type by rls name
		$type = $this->guessTypeByParsedAttributes();
		// 2nd: no type found? guess by section
		$type = empty( $type ) ? $this->guessTypeBySection( $section ) : $type;
		// 3rd: set parsed type or default to Movie
		$type = empty( $type ) ? 'Movie' : $type;

		$this->set( 'type', $type );
	}


	/**
	 * Guess the release type by alerady parsed attributes.
	 *
	 * @return string $type Guessed type.
	 */
	private function guessTypeByParsedAttributes(): string
	{
		$type = '';

		// Do We have an episode?
		if ( !empty( $this->get( 'episode' ) ) || !empty( $this->get( 'season' ) ) || $this->hasAttribute( self::SOURCES_TV, 'source' ) ) {

			// Default to TV
			$type = 'TV';

			// Anime (can have episodes) = if we additionaly have an anime flag in rls name
			if ( $this->hasAttribute( self::FLAGS_ANIME, 'flags' ) ) {
				$type = 'Anime';

			// Ebook (can have episodes) = if we additionaly have an ebook flag in rls name
			} else if( $this->hasAttribute( self::FLAGS_EBOOK, 'flags' ) ) {
				$type = 'eBook';

			// Abook (can have episodes) = if we additionaly have an abook flag in rls name
			} else if( $this->hasAttribute( 'ABOOK', 'flags' ) ) {
				$type = 'ABook';

			// Imageset (set number)
			} else if ( $this->hasAttribute( self::FLAGS_XXX, 'flags' ) ) {
				$type = 'XXX';

			// Description with date inside brackets is nearly always music or musicvideo
			} else if ( \preg_match( self::REGEX_DATE_MUSIC, $this->get( 'release' ) ) ) {
				$type = 'MusicVideo';
			}

		// Description with date inside brackets is nearly always music or musicvideo
		} else if ( \preg_match( self::REGEX_DATE_MUSIC, $this->get( 'release' ) ) ) {

			if ( !empty( $this->get( 'resolution' ) ) ) {
				$type = 'MusicVideo';
			} else {
				$type = 'Music';
			}

		// Has date and a resolution? probably TV
		} else if ( !empty( $this->get( 'date' ) ) && !empty( $this->get( 'resolution' ) ) ) {

			// Default to TV
			$type = 'TV';

			// Could be an xxx movie
			if ( $this->hasAttribute( self::FLAGS_XXX, 'flags' ) ) {
				$type = 'XXX';
			}

		// Check for MVid formats
		} else if ( $this->hasAttribute( self::FORMATS_MVID, 'format' ) ) {
			$type = 'MusicVideo';

		// Not TV, so first check for movie related flags
		} else if ( $this->hasAttribute( self::FLAGS_MOVIE, 'flags' ) ) {
			$type = 'Movie';

		// Music = if we found some music related flags
		} else if ( $this->hasAttribute( self::FLAGS_MUSIC, 'flags' ) || $this->hasAttribute( self::FORMATS_MUSIC, 'format' ) ) {
			$type = 'Music';

		// Ebook = ebook related flag
		} else if ( $this->hasAttribute( self::FLAGS_EBOOK, 'flags' ) ) {
			$type = 'eBook';

		// Abook = Abook related flag
		} else if ( $this->hasAttribute( 'ABOOK', 'flags' ) ) {
			$type = 'ABook';

		// Font = Font related flag
		} else if ( $this->hasAttribute( [ 'FONT', 'FONTSET' ], 'flags' ) ) {
			$type = 'Font';

		// Games = if device was found or game related flags
		} else if ( !empty( $this->get( 'device' ) ) || $this->hasAttribute( [ 'DLC', 'DLC Unlocker' ], 'flags' ) ) {
			$type = 'Game';

		// App = if os is set or software (also game) related flags
		} else if ( ( !empty( $this->get( 'version' ) ) || $this->hasAttribute( self::FLAGS_APPS, 'flags' ) ) && !$this->hasAttribute( self::FORMATS_VIDEO, 'format' ) ) {
			$type = 'App';

		// Porn = if JAV flag
		} else if ( $this->hasAttribute( self::FLAGS_XXX, 'flags' ) ) {
			$type = 'XXX';
		}

		// Check for Sports programs
		//$sports = [ 'NFL', 'NHL', 'MLB', 'Formula1', 'Premier.League', 'La[._-]?Liga', 'Eredivisie', 'Bundesliga', 'Ligue[._-]?1'];

		return $type;
	}


	/**
	 * Guess the release type by section.
	 *
	 * @param string $section Original release section.
	 * @return string $type Guessed/Parsed release type.
	 */
	private function guessTypeBySection( string &$section ): string
	{
		$type = '';

		// No Section, no chocolate!
		if ( !empty( $section ) ) {

			// Loop all types
			foreach ( self::TYPE as $typeParentKey => $typeValue ) {

				// Transform every var to array, so we can loop
				if ( !\is_array( $typeValue ) ) {
					$typeValue = [ $typeValue ];
				}

				// Loop all type patterns
				foreach ( $typeValue as $value ) {

					// Match type
					\preg_match( '/' . $value . '/i', $section, $matches );

					// Match found, set type parent key as type
					if ( !empty( $matches ) ) {
						$type = $typeParentKey;
						break;
					}
				}

				if ( !empty( $type ) ) break;
			}
		}

		return $type;
	}


	/**
	 * Parse release title.
	 *
	 * @return void
	 */
	private function parseTitle(): void
	{
		$type = \strtolower( $this->get( 'type' ) );
		$releaseNameCleaned = $this->release;

		// Main title vars
		$title = $titleExtra = \null;
		// Some vars for better debugging which regex pattern was used
		$regexPattern = $regexUsed = '';

		// We only break if we have some results.
		// If the case doenst't deliver results, it runs till default
		// which is the last escape and should deliver something.
		switch ( $type ) {

			// Music artist + release title (album/single/track name, etc.)
			case 'music':
			case 'abook':
			case 'musicvideo':

				// Setup regex pattern
				$regexPattern = self::REGEX_TITLE_MUSIC;
				$regexUsed = 'REGEX_TITLE_MUSIC';

				if ( $type === 'abook' ) {
					$regexPattern = self::REGEX_TITLE_ABOOK;
					$regexUsed = 'REGEX_TITLE_ABOOK';
				} else if ( $type === 'musicvideo' ) {
					$regexPattern = self::REGEX_TITLE_MVID;
					$regexUsed = 'REGEX_TITLE_MVID';
				}

				// Search and replace pattern in regex pattern for better macthing
				$regexPattern = $this->cleanupPattern( $this->release, $regexPattern, [ 'audio', 'flags', 'format', 'group', 'language', 'source' ] );

				// Special check for date:
				// If date is inside brackets with more words, it's part of the title.
				// If not, then we should consider and replace the regex date patterns inside the main regex pattern.
				if ( !\preg_match( self::REGEX_DATE_MUSIC, $releaseNameCleaned ) ) {
					$regexPattern = $this->cleanupPattern( $this->release, $regexPattern, [ 'regex_date', 'regex_date_monthname', 'year' ] );
				}

				// Match title
				\preg_match( $regexPattern, $releaseNameCleaned, $matches );

				if ( !empty( $matches ) ) {

					// Full match
					$title = $matches[1];

					// Split the title in the respective parts
					$titleSplitted = \explode( '-', $title );

					if ( !empty( $titleSplitted ) ) {

						// First value is the artist = title
						// We need the . for proper macthing cleanup episode.
						$title = $this->cleanup( '.' . $titleSplitted[0], 'episode' );

						// Unset this before the loop
						unset( $titleSplitted[0] );

						// Separator
						$separator = $type === 'abook' ? ' - ' : '-';

						// Loop remaining parts and set title extra
						foreach( $titleSplitted as $titlePart ) {

							// We need the . for proper macthing cleanup episode.
							$titlePart = $this->cleanup( '.' . $titlePart . '.', 'episode' );
							$titlePart = \trim( $titlePart, '.' );

							if ( !empty( $titlePart ) ) {
								$titleExtra = !empty( $titleExtra ) ? $titleExtra . $separator . $titlePart : $titlePart;
							}
						}
					}
					break;
				}

				// Jump to default if no title found
				if ( empty( $title ) ) goto standard;

			// Software (Game + Apps)
			case 'game':
			case 'app':

				// Setup regex pattern
				$regexPattern = self::REGEX_TITLE_APP;
				$regexUsed = 'REGEX_TITLE_APP';

				// Search and replace pattern in regex pattern for better macthing
				$regexPattern = $this->cleanupPattern( $this->release, $regexPattern, [ 'device', 'flags', 'format', 'group', 'language', 'os', 'source' ] );

				// Match title
				\preg_match( $regexPattern, $releaseNameCleaned, $matches );

				if ( !empty( $matches ) ) {
					$title = $matches[1];
					break;
				}

				// Jump to default if no title found
				if ( empty( $title ) ) goto standard;

			// TV series
			case 'tv':

				// Setup regex pattern
				$regexPattern = self::REGEX_TITLE_TV;
				$regexUsed = 'REGEX_TITLE_TV';

				// Match title
				\preg_match( $regexPattern, $releaseNameCleaned, $matches );

				// Check for matches with regex title tv
				if ( !empty( $matches ) ) {

					$title = $matches[1];

					// Build pattern and try to get episode title
					// So search and replace needed data to match properly.
					$regexPattern = self::REGEX_TITLE_TV_EPISODE;
					$regexUsed .= ' + REGEX_TITLE_TV_EPISODE';

					// Search and replace pattern in regex pattern for better macthing
					$regexPattern = $this->cleanupPattern( $this->release, $regexPattern, [ 'flags', 'format', 'language', 'resolution', 'source' ] );

					// Match episode title
					\preg_match( $regexPattern, $releaseNameCleaned, $matches );

					$titleExtra = !empty( $matches[1] ) ? $matches[1] : '';

					break;

				// Try to match Sports match
				} else {

					// Setup regex pattern
					$regexPattern = self::REGEX_TITLE_TV_DATE;
					$regexUsed = 'REGEX_TITLE_TV_DATE';

					// Search and replace pattern in regex pattern for better macthing
					$regexPattern = $this->cleanupPattern( $this->release, $regexPattern, [ 'flags', 'format', 'language', 'resolution', 'source', 'regex_date', 'year' ] );

					// Match Dated/Sports match title
					\preg_match( $regexPattern, $releaseNameCleaned, $matches );

					if ( !empty( $matches ) ) {

						// 1st match = event (nfl, mlb, etc.)
						$title = $matches[1];
						// 2nd match = specific event name (eg. team1 vs team2)
						$titleExtra = !empty( $matches[2] ) ? $matches[2] : '';

						break;

					}
				}

				// Jump to default if no title found
				if ( empty( $title ) ) goto standard;

			case 'anime':

				// Setup regex pattern
				$regexPattern = self::REGEX_TITLE_TV;
				$regexUsed = 'REGEX_TITLE_TV';

				// Match title
				\preg_match( $regexPattern, $releaseNameCleaned, $matches );

				// Check for matches with regex title tv
				if ( !empty( $matches ) ) {

					$title = $matches[1];

					// Build pattern and try to get episode title
					// So search and replace needed data to match properly.
					$regexPattern = self::REGEX_TITLE_TV_EPISODE;
					$regexUsed .= ' + REGEX_TITLE_TV_EPISODE';

					// Search and replace pattern in regex pattern for better macthing
					$regexPattern = $this->cleanupPattern( $this->release, $regexPattern, [ 'flags', 'format', 'language', 'resolution', 'source' ] );

					// Match episode title
					\preg_match( $regexPattern, $releaseNameCleaned, $matches );

					$titleExtra = !empty( $matches[1] ) ? $matches[1] : '';

					break;

				}

				// Jump to default if no title found
				if ( empty( $title ) ) goto standard;

			// XXX
			case 'xxx':

				// Setup regex pattern
				$regexPattern = !empty( $this->get( 'date' ) ) ? self::REGEX_TITLE_XXX_DATE : self::REGEX_TITLE_XXX;
				$regexUsed = !empty( $this->get( 'date' ) ) ? 'REGEX_TITLE_XXX_DATE' : 'REGEX_TITLE_XXX';

				// Search and replace pattern in regex pattern for better macthing
				$regexPattern = $this->cleanupPattern( $this->release, $regexPattern, [ 'flags', 'year', 'language', 'source', 'regex_date', 'regex_date_monthname' ] );

				// Match title
				\preg_match( $regexPattern, $releaseNameCleaned, $matches );

				if ( !empty( $matches ) ) {
					// 1st Match = Publisher, Website, etc.
					$title = $matches[1];
					// 2nd Match = Specific release name (movie/episode/model name, etc.)
					$titleExtra = !empty( $matches[2] ) ? $matches[2] : '';

					break;
				}

				// Jump to default if no title found
				if ( empty( $title ) ) goto standard;

			// Ebook
			case 'ebook':

				// Setup regex pattern
				$regexPattern = self::REGEX_TITLE_EBOOK;
				$regexUsed = 'REGEX_TITLE_EBOOK';

				// Cleanup release name for better matching
				$releaseNameCleaned = $this->cleanup( $releaseNameCleaned, 'episode' );

				// Search and replace pattern in regex pattern for better macthing
				$regexPattern = $this->cleanupPattern( $this->release, $regexPattern, [ 'flags', 'format', 'language', 'regex_date', 'regex_date_monthname', 'year' ] );

				// Match title
				\preg_match( $regexPattern, $releaseNameCleaned, $matches );

				if ( !empty( $matches ) ) {

					// Full match
					$title = $matches[1];

					// Split the title in the respective parts
					$titleSplitted = \explode( '-', $title );

					if ( !empty( $titleSplitted ) ) {

						// First value is the artist = title
						$title = $titleSplitted[0];
						// Unset this before the loop
						unset( $titleSplitted[0] );
						// Loop remaining parts and set title extra
						foreach( $titleSplitted as $titlePart ) {
							if ( !empty( $titlePart ) )
								$titleExtra = !empty( $titleExtra ) ? $titleExtra . ' - ' . $titlePart : $titlePart;
						}
					}
					break;
				}

				// Jump to default if no title found
				if ( empty( $title ) ) goto standard;

			// Font
			case 'font':

				// Setup regex pattern
				$regexPattern = self::REGEX_TITLE_FONT;
				$regexUsed = 'REGEX_TITLE_FONT';

				// Cleanup release name for better matching
				$releaseNameCleaned = $this->cleanup( $releaseNameCleaned, [ 'version', 'os', 'format' ] );

				// Match title
				\preg_match( $regexPattern, $releaseNameCleaned, $matches );

				if ( !empty( $matches ) ) {
					$title = $matches[1];
					break;
				}

				// Jump to default if no title found
				if ( empty( $title ) ) goto standard;

			// Movie
			default:

				// Jump in here for default matching
				standard:

				// Setup regex pattern
				$regexPattern = self::REGEX_TITLE_TV_DATE;
				$regexUsed = 'REGEX_TITLE_TV_DATE';

				// Search and replace pattern in regex pattern for better macthing
				$regexPattern = $this->cleanupPattern( $this->release, $regexPattern, [ 'flags', 'format', 'language', 'resolution', 'source' ] );

				// Cleanup release name for better matching
				if ( $type === 'xxx' ) {
					$releaseNameCleaned = $this->cleanup( $releaseNameCleaned, [ 'episode', 'monthname', 'daymonth' ] );
				}

				// Try first date format
				// NFL.2021.01.01.Team1.vs.Team2.1080p...
				$regexPattern = \str_replace( '%dateformat%', '(?:\d+[._-]){3}', $regexPattern );

				// Match Dated/Sports match title
				\preg_match( $regexPattern, $releaseNameCleaned, $matches );

				if ( !empty( $matches ) && !empty( $matches[2] ) ) {

					// 1st match = event (nfl, mlb, etc.)
					$title = $matches[1];
					// 2nd match = specific event name (eg. team1 vs team2)
					$titleExtra = $matches[2];

				} else {

					// Setup regex pattern
					$regexPattern = self::REGEX_TITLE_MOVIE;
					$regexUsed = 'REGEX_TITLE_MOVIE';

					// Search and replace pattern in regex pattern for better macthing
					$regexPattern = $this->cleanupPattern( $this->release, $regexPattern, [ 'flags', 'format', 'language', 'resolution', 'source', 'year' ] );

					// Match title
					\preg_match( $regexPattern, $releaseNameCleaned, $matches );

					if ( !empty( $matches ) ) {

						$title = $matches[1];

					// No matches? Try simplest regex pattern.
					} else {

						// Some very old (or very wrong named) releases dont have a group at the end.
						// But I still wanna match them, so we check for the '-'.
						$regexPattern = self::REGEX_TITLE;
						$regexUsed = 'REGEX_TITLE';

						// This should be default, because we found the '-'.
						if ( str_contains( $releaseNameCleaned, '-' ) ) {
							$regexPattern .= '-';
						}

						// Match title
						\preg_match( '/^' . $regexPattern . '/i', $releaseNameCleaned, $matches );

						// If nothing matches here, this release must be da real shit!
						$title = !empty( $matches ) ? $matches[1] : '';
					}
				}

		}

		// Only for debugging
		$this->set( 'debug', $regexUsed . ': ' . $regexPattern );

		// Sanitize and set title
		$this->set( 'title', $this->sanitize( $title ) );

		// Sanitize and set title extra
		// Title extra needs to have null as value if empty string.
		$titleExtra = empty( $titleExtra ) ? \null : $titleExtra;
		if ( isset( $titleExtra ) ) {
			$this->set( 'titleExtra', $this->sanitize( $titleExtra ) );
		}
	}


	/**
	 * Parse simple attribute.
	 *
	 * @param string $releaseName Original release name.
	 * @param array $attribute Attribute to parse.
	 * @return mixed $attributeKeys Found attribute value (string or array).
	 */
	private function parseAttribute( array $attribute )
	{
		$attributeKeys = [];

		// Loop all attributes
		foreach ( $attribute as $attrKey => $attrPattern) {

			// We need to catch the web source
			if ( $attrKey === 'WEB' ) {
				$attrPattern = $attrPattern . '[._\)-](%year%|%format%|%language%|%group%)';
				$attrPattern = $this->cleanupPattern( $this->release, $attrPattern, [ 'format', 'group', 'language',  'year' ] );
			}

			// Transform all attribute values to array (simpler, so we just loop everything)
			if ( ! \is_array( $attrPattern ) ) {
				$attrPattern = [ $attrPattern ];
			}

			// Loop attribute values
			foreach ( $attrPattern as $pattern ) {

				// Check if pattern is inside release name
				\preg_match( '/[._\(-]' . $pattern . '[._\)-]/i', $this->release, $matches );
				//\preg_match( '/[._\(-]' . $pattern . '[._\)-]/i', $this->release, $matches,  \PREG_OFFSET_CAPTURE );

				// Yes? Return attribute array key as value
				if ( !empty( $matches ) ) {
					$attributeKeys[] = $attrKey;
				}
			}
		}

		// Transform array to string if we have just one value
		if ( \count( $attributeKeys ) == 1 ) {
			$attributeKeys = \implode( $attributeKeys );
		}

		return $attributeKeys;
	}


	/**
	 * Check if rls has specified attribute value.
	 *
	 * @param mixed $value  Attribute values to check for (array or string)
	 * @return boolean If attribute values were found
	 */
	public function hasAttribute( $values, $attribute ) {

		// Get attribute value
		$attribute = $this->get( $attribute );

		// Check if attribute is set
		if ( isset( $attribute ) ) {

			// Transform var into array for loop
			if ( !\is_array( $values ) ) {
				$values = [ $values ];
			}

			// Loop all values to check for
			foreach ( $values as $value ) {
				// If values were saved as array, check if in array
				if ( \is_array( $attribute ) ) {
					foreach( $attribute as $attrValue ) {
						if ( \strtolower( $value ) === \strtolower( $attrValue ) ) return \true;
					}

				// If not, just check if the value is equal
				} else {
					if ( \strtolower( $value ) === \strtolower( $attribute ) ) return \true;
				}
			}
		}

		return \false;
	}


	/**
	 * Cleanup release name from given attribute.
	 * Mostly needed for better title macthing in some cases.
	 *
	 * @param string $releaseName Original release name.
	 * @param mixed $information Informations to clean up (string or array).
	 * @return string $releaseNameCleaned Cleaned up release name.
	 */
	private function cleanup( string $releaseName, $informations ): string
	{
		// Just return if no information name was passed.
		if ( empty( $informations ) || empty( $releaseName ) ) return $releaseName;

		// Transform var into array for loop
		if ( !\is_array( $informations ) ) {
			$informations = [ $informations ];
		}

		// Loop all attribute values to be cleaned up
		foreach ( $informations as $information ) {

			// Get information value
			$informationValue = $this->get( $information );
			// Get date as value if looking for "daymonth" or "month" (ebooks)
			if ( str_contains( $information, 'month' ) || str_contains( $information, 'date' ) ) {
				$informationValue = $this->get( 'date' );
			}

			// Only do something if it's not empty
			if ( isset( $informationValue ) && $informationValue != '' ) {

				$attributes = [];

				// Get proper attr value
				switch ( $information ) {

					case 'daymonth':
						// Clean up day and month number from rls
						$attributes = [
							$informationValue->format( 'd' ) . '(th|rd|nd|st)?',
							$informationValue->format( 'j' ) . '(th|rd|nd|st)?',
							$informationValue->format( 'm' )
						];
						break;

					case 'format':
						// Check if we need to loop array
						if ( \is_array( $informationValue ) ) {
							foreach ( $informationValue as $format ) {
								$attributes[] = self::FORMAT[ $format ];
							}
						} else {
							$attributes[] = self::FORMAT[ $informationValue ];
						}
						break;

					case 'episode':
						$attributes[] = self::REGEX_EPISODE;
						break;

					case 'flags':
						// Flags are always saved as array, so loop them.
						foreach ( $informationValue as $flag ) {
							// Skip some flags, needed for proper software/game title regex.
							if ( $flag != 'UPDATE' && $flag != '3D' )
								$attributes[] = self::FLAGS[ $flag ];
						}
						break;

					case 'language':
						foreach( $informationValue as $languageCodeKey => $language ) {
							$attributes[] = self::LANGUAGES[ $languageCodeKey ];
						}
						break;
						
					case 'monthname':
						// Replace all ( with (?: for non capturing
						$monthname = \preg_replace( '/\((?!\?)/i', '(?:', self::REGEX_DATE_MONTHNAME );
						// Get monthname pattern
						$monthname = \str_replace( '%monthname%', self::MONTHS[ $informationValue->format( 'n' ) ], $monthname );
						$attributes[] = $monthname;
						break;

					case 'os':
						// Some old releases have "for x" before the OS
						if ( \is_array( $informationValue ) ) {
							foreach( $informationValue as $value ) {
								$attributes[] = self::OS[ $value ];
							}
						} else {
							$attributes[] = self::OS[ $informationValue ];
						}
						break;

					case 'resolution':
						$attributes[] = self::RESOLUTION[ $informationValue ];
						break;
					
					case 'source':
						$attributes[] = self::SOURCE[ $informationValue ];
						break;
						
					case 'version':
						$attributes[] = self::REGEX_VERSION;
						break;
				}

				// Loop attributes if not empty and preg replace to cleanup
				if ( !empty( $attributes ) ) {

					foreach ( $attributes as $attribute ) {

						if ( \is_array( $attribute ) ) {
							foreach ( $attribute as $value ) {
								// Exception for OS
								if ( $information === 'os' )
									$value = '(?:for[._-])?' . $value;

								$releaseName = \preg_replace( '/[._\(-]' . $value . '[._\)-]/i', '..', $releaseName );
							}
						} else {
							// Exception for OS
							if ( $information === 'os' )
								$attribute = '(?:for[._-])?' . $attribute;

							$releaseName = \preg_replace( '/[._\(-]' . $attribute . '[._\)-]/i', '..', $releaseName );
						}
					}
				}
			}
		}

		return $releaseName;
	}


	/**
	 * Replace %attribute% in regex pattern with attribute pattern.
	 *
	 * @param string $releaseName Original release name.
	 * @param string $regexPattern The pattern to check.
	 * @param mixed $informations The information value to check for (string or array)
	 * @return string $regexPattern Edited pattern
	 */
	private function cleanupPattern( string $releaseName, string $regexPattern, $informations ): string
	{
		// Just return if no information name was passed.
		if (
			empty( $informations ) ||
			empty( $releaseName ) ||
			empty( $regexPattern ) ) return $regexPattern;

		// Transform to array
		if ( !\is_array( $informations ) ) {
			$informations = [ $informations ];
		}

		// Loop all information that need a replacement
		foreach ( $informations as $information ) {

			// Get information value
			$informationValue = $this->get( $information );
			// Get date as value if looking for "daymonth" or "month" (ebooks,imgset, sports)
			if ( str_contains( $information, 'month' ) || str_contains( $information, 'date' ) ) {
				$informationValue = $this->get( 'date' );
			}

			// Only do something if it's not empty
			if ( isset( $informationValue ) && $informationValue != '' ) {

				$attributes = [];

				switch( $information ) {

					case 'audio':
						// Check if we need to loop array
						if ( \is_array( $informationValue ) ) {
							foreach ( $informationValue as $audio ) {
								$attributes[] = self::AUDIO[ $audio ];
							}
						} else {
							$attributes[] = self::AUDIO[ $informationValue ];
						}
						break;

					case 'device':
						// Check if we need to loop array
						if ( \is_array( $informationValue ) ) {
							foreach ( $informationValue as $device ) {
								$attributes[] = self::DEVICE[ $device ];
							}
						} else {
							$attributes[] = self::DEVICE[ $informationValue ];
						}
						break;

					case 'flags':
						// Flags are always saved as array, so loop them.
						foreach ( $informationValue as $flag ) {
							// Skip some flags, needed for proper software/game title regex.
							if ( $flag != '3D' )
								$attributes[] = self::FLAGS[ $flag ];
						}
						break;

					case 'format':
						// Check if we need to loop array
						if ( \is_array( $informationValue ) ) {
							foreach ( $informationValue as $format ) {
								$attributes[] = self::FORMAT[ $format ];
							}
						} else {
							$attributes[] = self::FORMAT[ $informationValue ];
						}
						break;

					case 'group':
						$attributes[] = $informationValue;
						break;

					case 'language':
						// Get first parsed language code
						$languageCode = array_key_first( $informationValue );
						$attributes[] = self::LANGUAGES[ $languageCode ];
						break;

					case 'os':
						// Some old releases have "for x" before the OS
						if ( \is_array( $informationValue ) ) {
							foreach( $informationValue as $value ) {
								$attributes[] = self::OS[ $value ];
							}
						} else {
							$attributes[] = self::OS[ $informationValue ];
						}
						break;

					case 'resolution':
						$attributes[] = self::RESOLUTION[ $informationValue ];
						break;

					case 'regex_date':
						// Replace all ( with (?: for non capturing
						$attributes[] = \preg_replace( '/\((?!\?)/i', '(?:', self::REGEX_DATE );
						break;

					case 'regex_date_monthname':
						// Replace all ( with (?: for non capturing
						$regexDateMonthname = \preg_replace( '/\((?!\?)/i', '(?:', self::REGEX_DATE_MONTHNAME );
						// Get monthname pattern
						$regexDateMonthname = \str_replace( '%monthname%', self::MONTHS[ $informationValue->format( 'n' ) ], $regexDateMonthname );
						$attributes[] = $regexDateMonthname;

						break;

					case 'source':
						$attributes[] = self::SOURCE[ $informationValue ];
						break;

					case 'year':
						$attributes[] = $informationValue;
						break;

				}

				// Loop attributes if not empty and preg replace to cleanup
				if ( !empty( $attributes ) ) {

					$values = '';

					foreach ( $attributes as $attribute ) {

						if ( \is_array( $attribute ) ) {

							foreach ( $attribute as $value ) {

								$value = $information === 'os' ? '(?:for[._-])?' . $value : $value;

								// And check what exactly pattern matches the given release name.
								\preg_match( '/[._\(-]' . $value . '[._\)-]/i', $releaseName, $matches );
								// We have a match? ...
								if ( !empty( $matches ) ) {
									// Put to values and separate by | if needed.
									$values = !empty( $values ) ? $values . '|' . $value : $value;
								}
							}

						} else {
							$attribute = $information === 'os' ? '(?:for[._-])?' . $attribute : $attribute;

							// Put to values and separate by | if needed.
							$values = !empty( $values ) ? $values . '|' . $attribute : $attribute;
						}
					}

					// Replace found values in regex pattern
					$regexPattern = \str_replace( '%' . $information . '%', $values, $regexPattern );
				}
			}
		}

		return $regexPattern;
	}


	/**
	 * Sanitize the title.
	 *
	 * @param string $title Parsed title.
	 * @return string $title Sanitized title.
	 */
	private function sanitize( string $text ): string
	{
		if ( !empty( $text ) ) {

			// Trim '-' at the end of the string
			$text = \trim( $text, '-' );
			// Replace every separator char with whitespaces
			$text = \str_replace( [ '_', '.' ], ' ', $text );
			// Put extra whitespace between '-', looks better
			//$text = \str_replace( '-', ' - ', $text );
			// Trim and simplify multiple whitespaces
			$text = \trim( \preg_replace( '/\s{2,}/i', ' ', $text ) );

			// Check if all letters are uppercase:
			// First, check if we have more then 1 word in title (keep single worded titles uppercase).
			if ( \str_word_count( $text ) > 1 ) {
				// Remove all whitespaces and dashes for uppercase check to work properly.
				$text_temp = \str_replace( [ '-', ' ' ], '', $text );
				if ( \ctype_upper( $text_temp ) ) {
					// Transforms into lowercase, for ucwords to work properly.
					// Ucwords don't do anything if all chars are uppercase.
					$text = \ucwords( \strtolower( $text ) );
				}
			}

			$type = !empty( $this->get( 'type') ) ? $this->get( 'type') : '';
			
			// Words which should end with a point
			$specialWordsAfter = [ 'feat', 'ft', 'nr', 'st', 'pt', 'vol' ];
			if ( \strtolower( $type ) != 'app' ) {
				$specialWordsAfter[] = 'vs';
			}
			// Words which should have a point before (usualy xxx domains)
			$specialWordsBefore = [];
			if ( \strtolower( $type ) === 'xxx' ) {
				$specialWordsBefore = [ 'com', 'net', 'pl' ];
			}

			// Split title so we can loop
			$textSplitted = \explode( ' ', $text );

			// Loop, search and replace special words
			if ( \is_array( $textSplitted ) ) {
				foreach( $textSplitted as $textWord ) {
					// Point after word
					if ( \in_array( \strtolower( $textWord ), $specialWordsAfter ) ) {
						$text = \str_replace( $textWord, $textWord . '.', $text );

					// Point before word
					} else if ( \in_array( \strtolower( $textWord ), $specialWordsBefore ) ) {
						$text = \str_replace( ' ' . $textWord, '.' . $textWord , $text );
					}
				}
			}
		}

		return $text;
	}


	/**
	 * Get attribute value.
	 *
	 * @param string $name Attribute name.
	 * @return mixed Attribute value (array, string, int, date, null)
	 */
	public function get( string $name = 'all' )
	{
		// Check if var exists
		if ( isset( $this->data[ $name ] ) ) {
			return $this->data[ $name ];

		// Return all values
		} else if ( $name === 'all' ) {
			return $this->data;
		}

		return \null;
	}


	/**
	 * Set attribute value.
	 *
	 * @param string $name Attribute name to set.
	 * @param mixed $value Attribute value to set.
	 * @return true|false If value was succesfully set.
	 */
	private function set( string $name, $value )
	{
		// Check if array key alerady exists, so we don't create a new one
		if ( \array_key_exists( $name, $this->data ) ) {
			$this->data[ $name ] = $value;
			return \true;
		}
		return \false;
	}
}