<?php
namespace ReleaseParser;

/**
 * ReleasePatterns - All needed patterns for properly parsing releases.
 *
 * @package ReleaseParser
 * @author Wellington Estevo
 * @version 1.2.1
 */

class ReleasePatterns {

	// Declaration of some needed regex patterns.
	// https://regex101.com/ is your best friend for testing those patterns.
	// %varname% will be replaced with the parsed valued for better macthing.

	// Find language (old: (?!sub))
	const REGEX_LANGUAGE = '/[._(-]%language_pattern%[._)-][._(-]?(?:%source%|%format%|%audio%|%flags%|%year%|%os%|%device%|%resolution%|(?:us|gbr|eng|nl|fi|fr|no|dk|de|se|ice)|multi|ml[._)-]|dl[._)-]|dual[._-]|%group%)/i';
	// Find date
	const REGEX_DATE = '(\d{2}|\d{4})[._-](\d{2})[._-](\d{2}|\d{4})';
	// Special date with month name: 24th January 2002 / Sep. 2000 day 5 / January 2000 1
	const REGEX_DATE_MONTHNAME = '(\d{1,2})?(?:th|rd|st|nd)?[._-]?(%monthname%)[._-]?(\d{1,2})?(?:th|rd|st|nd)?[._-]?(\d{4})[._-]?(?:day[._-]?)?(\d{1,2})?';
	// Description with date inside brackets is nearly always music or musicvideo
	const REGEX_DATE_MUSIC = '/\([a-z._]+[._-]' . self::REGEX_DATE . '\)/i';
	// Get right year
	const REGEX_YEAR_SIMPLE = '(19\d[\dx]|20\d[\dx])';
	const REGEX_YEAR = '/(?=[(._-]' . self::REGEX_YEAR_SIMPLE . '[)._-])/i';
	// Extract group
	const REGEX_GROUP = '/-(\w+)$/i';
	// Extract OS
	//const REGEX_OS = '';
	// Episode pattern matches: S01E01 / 1x01 / E(PS)1 / OVA1 / F123 / Folge_123 / Episode 1 / Issue 1 etc.
	// Good for tv and audiobook rls
	const REGEX_EPISODE = '(?:(?:s\d+[._-]?)?(?:s?ep?|o[av]+[._-]?|f(?:olge[._-])?|band[._-]?|issue[._-]?|ausgabe[._-]?|n[or]?[._-]?|eps[._-]?|episode[._-]?|(?:silber[._-])?edition[._-]?|sets?[._-]?)([\d_-]+)|(?:\d+x)(\d+))';
	const REGEX_EPISODE_TV = '(?<!^)(?:(?:[ST]\d+)?[._-]?(?:ep?|o[av]+[._-]?|d|eps[._-]?|episode[._-]?)[\d-]+|\d+x\d+|[STD]\d+)';
	// For Disc numbers
	const REGEX_DISC = '(?:s\d+)?d(\d+)';
	// Season pattern matches: S01E01 / 1x01
	const REGEX_SEASON = '/[._-](?:[ST](\d+)[._-]?(?:[EDP]+\d+)?|(\d+)(?:x\d+))[._-]/i';
	// Basic title pattern
	const REGEX_TITLE = '([\w.()-]+)';
	// Good for Ebooks
	const REGEX_TITLE_EBOOK = '/^' . self::REGEX_TITLE . '[._(-]+(?:%year%|%language%|%flags%|%format%|%regex_date%|%regex_date_monthname%)[._)-]/iU';
	// Good for Fonts
	const REGEX_TITLE_FONT = '/^' . self::REGEX_TITLE . '-/i';
	// Good for Movies
	const REGEX_TITLE_MOVIE = '/^' . self::REGEX_TITLE . '[._(-]+(?:%year%|%language%|%source%|%flags%|%format%|%resolution%|%audio%)[._)-]/iU';
	const REGEX_TITLE_MOVIE_EXTRA = '/%year%[._-]' . self::REGEX_TITLE . '[._(-]\.+/iU'; // ungreedy
	// Music pattern matches: Author_2.0_(Name)-Track_album_title_2.0_-_track_bla_(Extended_edition)-...
	// Good for music releases and Audiobooks
	const REGEX_TITLE_MUSIC = '/^' . self::REGEX_TITLE . '(?:\([\w-]+\))?[._(-]+(?:\(?%source%\)?|%year%|%group%|%audio%|%flags%|%format%|%regex_date%|%regex_date_monthname%|%language%[._)-])/iU';
	const REGEX_TITLE_ABOOK = '/^' . self::REGEX_TITLE . '[._(-]+(?:%source%[._)-]|%year%|%group%|%audio%|%flags%|%format%|%language%[._)-])/iU';
	const REGEX_TITLE_MVID = '/^' . self::REGEX_TITLE . '[._(-]+(?:%source%|%year%|%group%|%audio%|%flags%|%format%|%regex_date%|%regex_date_monthname%|%language%[._)-])/iU';
	// Good for general Software releases (also Games)
	//const REGEX_TITLE_APP = '/^' . self::REGEX_TITLE . '[._\(-]+(?:' . self::REGEX_VERSION . '[._\(-]?\d|%language%|%flags%|%device%|%format%|%os%|%group%|%source%)/iU';
	const REGEX_TITLE_APP = '/^' . self::REGEX_TITLE . '[._(-]+(' . self::REGEX_VERSION . '|%device%|%os%)[._)-]/iU'; // ungreedy
	// Good for all kind of series (also Anime)
	const REGEX_TITLE_TV = '/^' . self::REGEX_TITLE . '[._-]' . self::REGEX_EPISODE_TV . '/iU';
	//const REGEX_TITLE_TV_EPISODE = '/' . self::REGEX_EPISODE_TV . '[._-](?:' . self::REGEX_TITLE . '[._\(-]+)?(?:%language%[._\)-]|%resolution%|%source%|%flags%|%format%)/iU';
	const REGEX_TITLE_TV_EPISODE = '/' . self::REGEX_EPISODE_TV . '[._-](?:' . self::REGEX_TITLE . '[._\(-]+)?\.+/iU'; // ungreedy
	const REGEX_TITLE_TV_DATE = '/^' . self::REGEX_TITLE . '[._\(-]+(?:%regex_date%|%year%)[._\)-]' . self::REGEX_TITLE . '?[._\(-]?(?:%language%[._\)-]|%resolution%|%source%|%flags%|%format%)/iU';
	// Good for XXX paysite releases
	const REGEX_TITLE_XXX = '/^' . self::REGEX_TITLE . '[._\(-]+(?:%year%|%language%[._\)-]|%flags%)/iU';
	//const REGEX_TITLE_XXX_DATE = '/^' . self::REGEX_TITLE . '[._-](?:\d+\.){3}' . self::REGEX_TITLE . '[._-](?:xxx|%language%)/iU';
	const REGEX_TITLE_XXX_DATE = '/^' . self::REGEX_TITLE . '[._\(-]+(?:%regex_date%|%regex_date_monthname%)[._\)-]+' . self::REGEX_TITLE . '[._\(-]+(?:%flags%|%language%[._\)-])/iU';
	// Extract software version
	const REGEX_VERSION_TEXT = '(?:v(?:ersione?)?|Updated?[._-]?v?|Build)';
	const REGEX_VERSION = self::REGEX_VERSION_TEXT . '[._-]?([\d.]+[a-z\d]{0,3}(?![._-]gage))';
	// Football, Cricket, Racing, Hockey, Basketball etc
	const REGEX_SPORTS = '/^(NFL|NBA.(?:East|West|Finals)|WNBA.\d{4}|NHL|ML[BS]|Formula.?[1234E]|F[123]|Superleague.Formula|Nascar.(?:cup|truck|xfinity)|Indycar|Moto.?(GP|[123])|Supercars.championship|W.series.\d{4}|Premier.?League|La.?Liga|Eredivisie|Bundesliga|Ligue.?1|Seria.?A|FA.Cup|EPL|EFL.(?:\d{1,4}|cup|championship)|NRL|(?:Super|international).rugby|CSL|IPL|BBL|T20|LPL.PRO|Eurocup|World.cup|fifa.(?:world.cup|women|fotbolls|wm|U\d+)|(?:international.)?football.(?:australia|womens|sydney|friendly|ligue1|serie.a|uefa|conference|league)|(?:womens.)?UEFA|UEL|concacaf|conmebol|caf|afc.asian|a-league|Grand.Sumo|Cycling.(?:volta|giro|tour|strade|paris|criterium|liege|fleche|amstel|la.vuelta)|giro.d.italia|la.vuelta.(?:a.espana.)?\d{4}|tour.de.france.(?:femmes.)?\d{4}.stage.?\d+|UCI|boxing.\d{4}.\d{2}.\d{2}|wimbledon.(?:tennis.)?\d{4}|wwe.(?:nxt|friday|this|main|monday|wrestlemania))[._-]/i';


	// Type patterns.
	// Default type will be set to 'Movie' if no other matches.
	const TYPE = [
		// Audiobook
		'ABook' => 'a.*book',
		// Anime
		'Anime' => 'anime',
		// Software Sections
		'App' => [ 'app', '0day', 'pda' ],
		// Ebook
		'eBook' => 'book',
		// Font
		'Font' => 'font',
		// Game/Console Sections
		'Game' => [ 'GAME', 'D[SC]', 'G[BC]', 'NSW', 'PS', 'XBOX', 'WII' ],
		// Music Sections
		'Music' => [ 'mp3', 'flac', 'music' ],
		// Music Video Sections
		'MusicVideo' => 'm(vid|dvd|bluray)',
		// TV Sections
		'TV' => 'tv',
		// Sports
		'Sports' => 'sport',
		// XXX Sections
		'XXX' => [ 'xxx', 'imgset' ],
		// Movie Sections
		'Movie' => [ 'movie', '(?!tv).*26[45]', 'bluray', 'dvdr', 'xvid', 'divx' ],
	];

	// Video/Audio source patterns
	const SOURCE = [
		'ABC' => 'ABC', // American Broadcasting Company (P2P)
		'Amazon' => [ 'AZ', 'AMZN', 'AmazonHD' ], // (P2P)
		'Amazon Freevee' => 'Freevee', // (P2P)
		'ATVP' => 'ATVP', // Apple TV
		'AUD' => 'AUD', // Audience Microphone
		'BBC' => 'BBC', // British Broadcasting Company (P2P)
		'BBC iPlayer' => 'iP', // (P2P)
		'BDRip' => 'b[dr]+[._-]?rip',
		'Bluray Screener' => [ 'bluray[._-]?scr', 'bd[._-]?scr' ],
		'BookMyShow' => 'BMS', // (P2P)
		'Bootleg' => '(?:LIVE|\d*cd)?[._-]?BOOTLEG',
		'CABLE' => 'cable',
		'CAM' => '(?:new)?cam([._-]?rip)?',
		'CBS' => 'CBS', // CBS Corporation (P2P)
		'CD Album' => '\d*cda', // CD Album
		'CD EP' => 'cdep',
		'CD Single' => [ 'cds', '(?:cd[._-]?)single' ], // CD Single
		'Comedy Central' => 'CC', // (P2P)
		'Console DVD' => [ 'xboxdvdr?', 'ps2[._-]?dvd' ],
		'Crave' => 'CRAV', // (P2P)
		'Crunchyroll' => 'CR', // (P2P)
		'DAT Tape' => '\d*DAT', // Digital Audio Tape
		'DAB' => 'dab', // Digital Audio Broadcast
		'DC Universe' => 'DCU', // (P2P)
		'DD' => 'dd(?![._-]?\d)', // Digital Download
		'DDC' => 'ddc', // Downloadable/Direct Digital Content
		'Disney Plus' => [ 'DP', 'DSNP' ], // (P2P)
		'Disney Networks' => 'DSNY', // (P2P)
		'Discovery Plus' => 'DSCP', // (P2P)
		'DSR' => [ 'dsr', 'dth', 'dsr?[._-]?rip', 'sat[._-]?rip', 'dth[._-]?rip' ], // Digital satellite rip (DSR, also called SATRip or DTH)
		'DVB' => [ 'dvb[sct]?(?:[._-]?rip)?', 'dtv', 'digi?[._-]?rip' ],
		'DVDA' => '\d*dvd[_-]?a', // Audio DVD
		'DVDS' => 'dvd[_-]?s', // DVD Single
		'DVDRip' => '(?:r\d[._-])?dvd[._-]?rip(?:xxx)?',
		'DVD Screener' => [ 'dvd[._-]?scr', '(?:dvd[._-]?)?screener', 'scr' ],
		'EDTV' => 'EDTV(?:[._-]rip)?', // Enhanced-definition television
		'EP' => 'EP',
		'FM' => '\d*FM', // Analog Radio
		'Google Play' => 'GPLAY', // (P2P)
		'HBO Max' => [ 'HM', 'HMAX', 'HBOM', 'HBO[._-]Max' ], // (P2P)
		'HDCAM' => 'HDCAM',
		'HDDVD' => '\d*hd[\d._-]?dvdr?',
		'HDRip' => [ 'hd[._-]?rip', 'hdlight', 'mhd', '(?<!dts[._-])hd' ],
		'HDTC' => 'HDTC(?:[._-]?rip)?', // High Definition Telecine
		'HDTV' => 'a?hd[._-]?tv(?:[._-]?rip)?',
		'HLS' => 'HLS', // HTTP Live Streaming
		'Hotstar' => 'HTSR', // (P2P)
		'Hulu' => 'Hulu(?:UHD)?', // (P2P)
		'iTunes' => [ 'iT', 'iTunes(?:HD)?' ], // (P2P)
		'Lionsgate Play' => 'LGP', // Lionsgate Play (P2P)
		'Line' => 'line(?![._-]dubbed)',
		'LP' => '\d*lp', // Vinyl Album
		'Maxi CD' => [ 'cdm', 'mcd', 'maxi[._-]?single', '(?:cd[._-]?)?maxi' ], // CD Maxi
		'Maxi Single' => [ 'maxi[._-]?single|single[._-]?maxi', '(?<!cd[._-])maxi', '12[._-]?inch' ], // Maxi Single (Vinyl) / 12 inch
		'MBluray' => 'MBLURAY',
		'MDVDR' => 'MDVDR?',
		'Movies Anywhere' => '(?<!DTS[._-]|HD[._-])MA', // (P2P)
		'MP3 CD' => '\d*mp3cd',
		'MTV Networks' => 'MTV', // (P2P)
		'Mubi' => 'MUBI', // (P2P)
		'NBC' => 'NBC', // National Broadcasting Company (P2P)
		'Netflix' => [ 'NF', 'NFLX', 'Netflix(?:HD)?' ], // (P2P)
		'Nintendo eShop' => 'eshop', // Nintendo eShop
		'Paramount Plus' => 'PMTP', // (P2P)
		'Peacock' => 'PCOK', // (P2P)
		'PDTV' => 'PDTV',
		'PPV' => 'PPV(?:[._-]?RIP)?', // Pay-per-view
		'PSN' => 'PSN', // Playstation Network
		'SAT' => 'sat', // Analog Satellite
		'Scan' => 'scan',
		'Showtime' => 'SHO', // (P2P)
		'SDTV' => '(?:sd)?tv(?:[._-]?rip)?',
		'SBD' => 'SBD', // Soundboard
		'Stan' => 'Stan(?:HD)?', // (P2P)
		'Stream' => 'stream',
		'Starz' => 'STA?R?Z', // (P2P)
		'TBS' => 'TBS', // Turner Broadcasting System
		'Telecine' => [ 'tc', 'telecine' ],
		'Telesync' => [ '(?:hd[._-])?ts', 'telesync', 'pdvd' ], // ‘CAM’ video release with ‘Line’ audio synced to it.
		'UHDBD' => 'UHD[\d._-]?BD',
		'UHDTV' => 'UHD[._-]?TV',
		'VHS' => 'VHS(?:[._-]?rip)?',
		'VLS' => 'vls', // Vinyl Single
		'Vinyl' => [ '(Complete[._-])?Vinyl', '12inch' ],
		'VODRip' => [ 'VOD.?RIP', 'VODR' ],
		'Web Single' => '(?:web.single|single.web)', // Web single
		// If we have more than 1 source with WEB: the general WEB source needs to be the last one to be parsed
		'WEB' => 'WEB[._-]?(?!single)(?:tv|dl|u?hd|rip|cap|flac|mux)?',
		'WOW tv' => 'WOWTV', // (P2P)
		'XBLA' => 'XBLA', // Xbox Live Arcade
		'YouTube Red' => 'YTred', // (P2P)
		'MiniDisc' => [ 'md', 'minidisc' ], // Needs to be at the end, since some music releases has MD as source, but normally is MicDubbed for movies, so would wrongfully parse
		// Misc Fallback
		'CD' => [ '\d*cdr?\d*', 'cd[._-]?rom' ], // Other CD
		'DVD' => '(Complete[._-])?\d*dvd[_-]?[r\d]?', // Just normal DVD
		'Bluray' => [ 'blu[._-]?ray', '\d*bdr' ],
		'RiP' => 'rip', // If no other rip matches
	];

	// Video Encoding patterns
	// https://en.wikipedia.org/wiki/List_of_codecs#Video_compression_formats
	const FORMAT = [
		// Video formats
		'AVC' => 'AVC',
		'XViD' => 'XViD',
		'DiVX' => 'DiVX\d*',
		'x264' => 'x\.?264',
		'x265' => 'x\.?265',
		'h264' => 'h\.?264',
		'h265' => 'h\.?265',
		'HEVC' => '(?:HDR10)?HEVC',
		'HEVC' => 'HEVC',
		'VP8' => 'VP8',
		'VP9' => 'VP9',
		'MP4' => 'MP4',
		'MPEG' => 'MPEG',
		'MPEG2' => 'MPEG2',
		'VCD' => 'VCD',
		'CVD' => 'CVD',
		'CVCD' => 'CVCD', // Compressed Video CD
		'SVCD' => 'X?SVCD',
		'VC1' => '(?:Bluray[._-])?VC[._-]?1',
		'WMV' => 'WMV',
		'MDVDR' => 'MDVDR?',
		'DVDR' => 'DVD[R\d]',
		'MBluray' => '(Complete[._-])?MBLURAY',
		'Bluray' => '(complete[._-]?)?bluray',
		'MViD' => 'MViD',
		// Ebook formats
		'AZW' => 'AZW',
		'Comic Book Archive' => 'CB[artz7]',
		'CHM' => 'CHM',
		'ePUB' => 'EPUB',
		'Hybrid' => 'HYBRID',
		'LIT' => 'LIT',
		'MOBI' => 'MOBI',
		'PDB' => 'PDB',
		'PDF' => 'PDF',
		// Music formats
		'DAISY' => 'DAISY', // Audiobook
		'FLAC' => '(?:WEB[._-]?)?FLAC',
		'KONTAKT' => 'KONTAKT',
		'MP3' => 'MP3',
		'WAV' => 'WAV',
		// Software format
		'ISO' => '(?:Bootable[._-])?ISO',
		// Font format
		'CrossPlatform' => 'Cross(?:Format|Platform)',
		'OpenType' => 'Open[._-]?Type',
		'TrueType' => 'True[._-]?Type',
		// Software/Game format
		'Java Platform, Micro Edition' => 'j2me(?:v\d*)?',
		'Java' => 'JAVA',
		// Misc
		'Multiformat' => 'MULTIFORMAT'
	];

	// Video resolution patterns
	const RESOLUTION = [
		'SD' => 'SD',
		'NTSC' => 'NTSC',	// = 480p
		'PAL' => 'PAL',		// = 576p
		'480p' => '480p',
		'576p' => '576p',
		'720p' => '720p',
		'1080i' => '1080i',
		'1080p' => '1080p',
		'2160p' => '2160p',
		'2700p' => '2700p',
		'2880p' => '2880p',
		'3072p' => '3072p',
		'3160p' => '3160p',
		'3600p' => '3600p',
		'4320p' => '4320p'
	];

	// Audio quality patterns
	const AUDIO = [
		'10BIT' => '10B(?:IT)?',
		'16BIT' => '16B(?:IT)?',
		'24BIT' => '24B(?:IT)?',
		'44K' => '44kHz',
		'48K' => '48kHz',
		'96K' => '96KHZ',
		'160K' => '16\dk(?:bps)?',
		'176K' => '176khz',
		'192K' => '19\dk(?:bps)?',
		'AAC' => 'AAC(?:\d)*',
		'AC3' => 'AC3(?:dub|dubbed|MD)?',
		'AC3D' => 'AC3D',
		'EAC3' => 'EAC3',
		'EAC3D' => 'EAC3D',
		'Dolby Atmos' => 'ATMOS',
		'Dolby Digital' => [ 'DOLBY.?DIGITAL', 'dd[^p]?\d+' ],
		'Dolby Digital Plus' => [ 'DOLBY.?DIGITAL', 'ddp.?\d' ],
		'Dolby Digital Plus, Dolby Atmos' => 'ddpa.?\d',
		'Dolby trueHD' => '(?:Dolby)?[._-]?trueHD',
		'DTS' => 'DTSD?(?!.?ES|.?HD|.?MA)[._-]?\d*',
		'DTS-ES' => 'DTS.?ES(?:.?Discrete)?',
		'DTS-HD' => 'DTS.?(?!MA)HD(?!.?MA)',
		'DTS-HD MA' => [ 'DTS.?HD.?MA', 'DTS.?MAD?' ],
		'DTS:X' => 'DTS[._-]?X',
		'OGG' => 'OGG',
		// Channels
		'2.0' => [ 'd+2[._-]?0', '\w*(?<!v[._-]|v)2[._-]0', '2ch' ],
		'2.1' => [ 'd+2[._-]?1', '\w*(?<!v[._-]|v)2[._-]1' ],
		'3.1' => [ 'd+3[._-]?1', '\w*(?<!v[._-]|v)3[._-]1' ],
		'5.1' => [ 'd+5[._-]?1', '\w*(?<!v[._-]|v)5[._-]1(?:ch)?' ],
		'7.1' => [ 'd+7[._-]?1', '\w*(?<!v[._-]|v)7[._-]1' ],
		'7.2' => [ 'd+7[._-]?2', '\w*(?<!v[._-]|v)7[._-]2' ],
		'9.1' => [ 'd+9[._-]?1', '\w*(?<!v[._-]|v)9[._-]1' ],
		// Misc
		'Dual Audio' => '(Dual[._-]|2)Audio',
		'Tripple Audio' => '(Tri|3)Audio'
	];

	// Game Console patterns
	const DEVICE = [
		'3DO' => '3DO',
		'Bandai WonderSwan' => 'WS',
		'Bandai WonderSwan Color' => 'WSC',
		'Commodore Amiga' => 'AMIGA',
		'Commodore Amiga CD32' => 'CD32',
		'Commodore C64' => 'C64',
		'Commodore C264' => 'C264',
		'Nintendo DS' => 'NDS',
		'Nintendo 3DS' => '3DS',
		'Nintendo Entertainment System' => 'NES',
		'Super Nintendo Entertainment System' => 'SNES',
		'Nintendo GameBoy' => [ 'GB', 'GAMEBOY' ],
		'Nintendo GameBoy Color' => 'GBC',
		'Nintendo GameBoy Advanced' => 'GBA',
		'Nintendo Gamecube' => [ 'NGC', 'GAMECUBE' ],
		'Nintendo iQue Player' => 'iQP',
		'Nintendo Switch' => 'NSW',
		'Nintendo WII' => 'WII',
		'Nintendo WII-U' => 'WII[._-]?U',
		'NEC PC Engine' => 'PCECD',
		'Nokia N-Gage' => '(?:nokia[._-])?n[._-]?gage(?:[._-]qd)?',
		'Playstation' => 'PS[X1]?',
		'Playstation 2' => 'PS2',
		'Playstation 3' => 'PS3',
		'Playstation 4' => 'PS4',
		'Playstation 5' => 'PS5',
		'Playstation Portable' => 'PSP',
		'Playstation Vita' => 'PSV',
		'Pocket PC' => 'PPC\d*',
		'Sega Dreamcast' => [ 'DC', 'DREAMCAST' ],
		'Sega Mega CD' => 'MCD',
		'Sega Mega Drive' => 'SMD',
		'Sega Saturn' => 'SATURN',
		'Tiger Telematics Gizmondo' => 'GIZMONDO',
		'VTech V.Flash' => 'VVD',
		'Microsoft Xbox' => 'XBOX',
		'Microsoft Xbox One' => 'XBOXONE',
		'Microsoft Xbox360' => [ 'XBOX360', 'X360' ],
	];

	// Operating System patterns for Software/Game releases
	const OS = [
		'IBM AIX' => 'AIX', // Advanced Interactive eXecutive
		'Android' => 'Android',
		'BlackBerry' => 'Blackberry',
		'BSD' => '(?:Free|Net|Open)?BSD',
		'HP-UX' => 'HPUX', // Hewlett Packard Unix
		'iOS' => [ 'iOS', 'iPhone' ],
		'Linux' => 'Linux(?:es)?',
		'macOS' => 'mac([._-]?osx?)?',
		'PalmOS' => 'Palm[._-]?OS\d*',
		'Solaris' => [ '(Open)?Solaris', 'SOL' ],
		'SunOS' => 'Sun(OS)?',
		'Symbian' => 'Symbian(?:OS\d*[._-]?\d*)?',
		'Ubuntu' => 'Ubuntu',
		'Unix'	=> 'Unix(All)?',
		'WebOS' => 'WebOS',
		// Found these hillarious (but rule conform) windows tags for software releases:
		// win9xnt2000 / WinNT2kXPvista / Win2kXP2k3Vista / winxp98nt2kse / win2kxpvista / Win2KXP2003Vista / WinXP2k3Vista2k8
		'Windows' => 'win(?:(?:[\d]+[\dxk]?|nt|all|dows|xp|vista|[msp]e)?[._-]?){0,6}',
		'Windows CE' => 'wince',
		'Windows Mobile' => 'wm\d+([._-]?se)?',
	];

	// Release language + language code patterns
	// https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes
	const LANGUAGES = [
		'am' => 'Amharic',
		'ar' => 'Arabic',
		'ch' => [ 'Swiss', 'CH' ],
		'cs' => [ 'Czech', 'CZ' ],
		'cy' => 'Welsh',
		'de' => [ 'German', 'GER', 'DE' ],
		'dk' => [ 'Danish', 'DK' ],
		'el' => [ 'Greek', 'GR' ],
		'en' => [ 'English', 'VOA', 'USA?', 'AUS', 'UK', 'GBR', 'ENG' ],
		'es' => [ 'Spanish', 'Espanol', 'ES', 'SPA', 'Latin[._-]Spanish' ],
		'et' => [ 'Estonian', 'EE' ],
		'fa' => [ 'Persian', 'Iranian', 'IR' ],
		'fi' => [ 'Finnish', 'FIN?' ],
		'fil' => 'Filipino',
		'fr' => [ 'French', 'Fran[cç]ais', 'TRUEFRENCH', 'VFF', '(ST|VOS)?FR[EA]?' ],
		'ga' => 'Irish',
		'he' => 'Hebrew',
		'hi' => 'Hindi',
		'ht' => 'Creole',
		'hu' => 'Hungarian',
		'id' => 'Indonesian',
		'is' => [ 'Icelandic', 'ICE' ],
		'it' => [ 'Italian', 'ITA?' ],
		'jp' => [ 'Japanese', 'JA?PN?' ],
		'ko' => [ 'Korean', 'KOR' ],
		'km' => 'Cambodian',
		'lo' => 'Laotian',
		'lt' => [ 'Lithuanian', 'LIT' ],
		'lv' => 'Latvian',
		'mi' => 'Maori',
		'ms' => [ 'Malay', 'Malaysian' ],
		'nl' => [ 'Dutch', 'HOL', 'NL', 'Flemish', 'FL' ],
		'no' => [ 'Norwegian', 'NOR?(?![._-]?\d+)' ],
		'ps' => 'Pashto',
		'pl' => [ 'Polish', 'PO?L' ],
		'pt' => [ 'Portuguese', 'PT' ],
		'pt-BR' => [ 'Brazilian', 'BR' ],
		'ro' => 'Romanian',
		'ru' => [ 'Russian', 'RU' ],
		'sk' => [ 'Slovak', 'SK', 'SLO', 'Slovenian' ],
		'sv' => [ 'Swedish', 'SW?E' ],
		'sw' => 'Swahili',
		'tg' => 'Tajik',
		'th' => 'Thai',
		'tl' => 'Tagalog',
		'tr' => [ 'Turkish', 'Turk', 'TR' ],
		'uk' => 'Ukrainian',
		'vi' => 'Vietnamese',
		'zh' => [ 'Chinese', 'CH[ST]' ],
		// Misc
		'multi' => [ 'Multilingual', 'Multi[._-]?(?:languages?|lang|\d*)?', 'EURO?P?A?E?', '(?<!WEB[._-])[MD]L', 'DUAL(?![._-]Audio)' ],
		'nordic' => [ 'Nordic', 'SCANDiNAViAN' ]

	];

	// Release flags patterns
	const FLAGS = [
		'3D' => '3D',
		'ABook' => 'A(?:UDiO)?BOOK',
		'Abridged' => [ 'ABRIDGED', 'gekuerzte?(?:[._-](?:fassung|lesung))' ], // Audiobook
		'Addon' => 'ADDON', // software
		'Anime' => 'ANiME',
		'ARM' => 'ARM', // software
		'Audiopack' => 'Audio[._-]?pack', // Only audio releases for movies
		'Beta' => 'BETA', // software
		'Bookware' => 'BOOKWARE', // software
		'Boxset' => 'BOXSET',
		'Chapterfix' => 'CHAPTER[._-]?FIX', // Disc
		'Cheats' => 'Cheats', // games
		'Chrono' => 'CHRONO',
		'Colorized' => 'COLORIZED',
		'Comic' => 'COMIC',
		'Complete' => 'Complete',
		'Convert' => 'CONVERT',
		'Cover' => '(?:CUSTOM[._-]?|[a-z]+)?COVERS?',
		'CPOP' => 'CPOP', // Chinese-pop
		'Chapterfix' => 'CHAPTER[._-]?FIX', // Disc
		'Incl. Crack' => [ 'CRACK[._-]ONLY', '(?:incl|working)[._-](?:[a-zA-Z]+[._-])?crack' ], // software
		'Chapterfix' => 'CHAPTER[._-]?FIX', // Disc
		'Cracked' => 'CRACKED', // software
		'Crackfix' => 'CRACK[._-]?FIX', // software
		'Criterion' => 'CRITERION', // special movie rls
		'Digipack' => 'DIGIPAC?K?', // music
		'Directors Cut' => 'Directors?[._-]?cut',
		'DIRFiX' => 'DIR[._-]?FIX',
		'DIZFiX' => 'DIZ[._-]?FIX',
		'DLC' => '(?:incl[._-])?DLCS?(?![._-]?(?:Unlocker|Pack))?', // games
		'DOC' => 'D[O0][CX]',
		'Doku' => 'DO[CK]U',
		'Dolby Vision' => [ 'DV', 'DoVi' ],
		'Dubbed' => [ '(?<!line[._-]|line|mic[._-]|mic|micro[._-]|tv[._-])Dubbed', 'E[._-]?Dubbed', '(?!over|thunder)[a-z]+dub' ],
		'eBook' => 'EBOOK',
		'Extended' => 'EXTENDED(?:[._-]CUT|[._-]Edition)?(?![._-]MIX)',
		'Final' => 'FINAL[._-]?(%language%)',
		'FiX' => '(?<!hot[._-]|sample[._-]|nfo[._-]|rar[._-]|dir[._-]|crack[._-]|sound[._-]|track[._-]|diz[._-]|menu[._-])FiX(?:[._-]?only)?',
		'Font' => '(Commercial[._-])?FONTS?',
		'Fontset' => '(Commercial[._-])?FONT[._-]?SET',
		'Fullscreen' => 'FS', // Fullscreen
		'FSK' => 'FSK', // German rating system
		'Hardcoded Subtitles' => 'HC', // (P2P)
		'HDLIGHT' => 'HDLIGHT',
		'HDR' => 'HDR',
		'HDR10' => 'HDR10(?:hevc)?',
		'HDR10+' => 'HDR10(Plus|\+)',
		'HLG' => 'HLG', // Hybrid log-gamma (like HDR)
		'HOTFiX' => 'HOT[._-]?FIX',
		'HOU' => 'HOU',
		'HSBS' => 'HS(?:BS)?',
		'Hybrid' => 'HYBRID',
		'Imageset' => '(?:Full[._-]?)?(?:IMA?GE?|photo|foto)[._-]?SETS?',
		'IMAX' => 'IMAX',
		'Internal' => 'iNT(ERNAL)?',
		'IVTC' => 'IVTC', // Inverce telecine
		'JAV' => 'JAV', // Japanese Adult Video
		'KEY' => 'GENERIC[._-]?KEY',
		'KEYGEN' => [ '(?:Incl[._-])?KEY(?:GEN(?:ERATOR)?|MAKER)(?:[._-]only)?', 'KEYFILE[._-]?MAKER' ],
		'Intel' => 'INTEL',
		'Line dubbed' => [ 'ld', 'line[._-]?dubbed' ],
		'Limited' => 'LIMITED',
		'Magazine' => 'MAG(AZINE)?',
		'Menufix' => 'MENU[._-]?FIX',
		'Micro dubbed' => [ '(?:ac3)?md', 'mic(ro)?[._-]?dubbed' ],
		'MIPS' => 'MIPS', // software (MIPS CPU)
		'New' => 'New[._-](%format%|%language%|%source%|%resolution%)',
		'NFOFiX' => 'NFO[._-]?FiX',
		'OAR' => 'OAR', // Original Aspect Ratio
		'OVA' => 'O[AV]+', // Original Video Anime/Original Anime Video
		'OAD' => 'OAD', // Original Anime DVD
		'ONA' => 'OMA', // Original Net Animation
		'OEM' => 'OEM',
		'OST' => 'OST', // music
		//'PACK' => 'PACK',
		'Incl. Patch' => [ '(?:incl[._-])?(?:[a-z]+[._-])?patch(?:ed)?(?:[._-]only)', 'no[a-zA-Z]+[._-]patch(?:ed)?(?:[._-]only)' ], // software
		'Paysite' => 'PAYSITE', // xxx
		'Portable' => 'Portable', // Software
		'Preair' => 'PREAIR',
		'Proper' => '(?:REAL)?PROPER',
		'Promo' => 'PROMO',
		'Prooffix' => 'PROOF[._-]?FIX',
		'Rated' => 'RATED',
		'RARFix' => 'RARFIX',
		'READNFO' => 'READ[._-]?NFO',
		'Refill' => 'Refill',
		'Reissue' => 'REISSUE',	// music
		'Regged' => 'REGGED', // software
		'Regraded' => 'regraded', // Movie (p2p)
		'Remastered' => 'REMASTERED',
		'Remux' => 'REMUX',
		'Repack' => '(working[._-])?REPACK',
		'RERiP' => 're[._-]?rip',
		'Restored' => 'RESTORED',
		'Retail' => 'RETAIL',
		'Samplefix' => 'SAMPLE[._-]?FIX',
		'SDR' => 'SDR',
		'Serial' => 'SERIAL(?![._-]Killer)?', // Software
		'SFVFix' => 'SFV[._-]?FIX',
		'SH3' => 'SH3', // software (SH3 CPU)
		'Soundfix' => 'SOUNDFIX',
		'Special Edition' => 'SE(?![._-]\d*)',
		'STV' => 'STV',
		'Subbed' => [ '[a-zA-Z]*SUB(?:BED|S)?', 'SUB[._-]?\w+' ],
		'Subfix' => 'SUB[._-]?FIX',
		'Superbit' => 'Superbit',	// https://de.wikipedia.org/wiki/Superbit
		'Syncfix' => 'SYNC[._-]?FIX', // Video AUdio
		'Theatrical' => 'THEATRICAL',
		'Trackfix' => 'TRACK[._-]?FiX', // Music
		'Trailer' => 'TRAILER',
		'Tutorial' => 'TUTORIAL',
		'TV Dubbed' => 'tv[._-]?dubbed',
		'UHD' => 'UHD',
		'Upscaled UHD' => 'UpsUHD',
		'Unabridged' => [ 'UNABRIDGED', 'Ungekuerzt' ], // Audiobook
		'Uncensored' => 'UNCENSORED',
		'Uncut' => 'UNCUT',
		'Unlicensed' => 'UNLiCENSED',
		'Unrated' => 'UNRATED',
		'Untouched' => 'UNTOUCHED',
		'USK' => 'USK', // German rating system
		'Update' => '(WITH[._-])?UPDATE',
		'V1' => 'V1[._-](%format%|%language%|%source%|%resolution%)',
		'V2' => 'V2[._-](%format%|%language%|%source%|%resolution%)',
		'V3' => 'V3[._-](%format%|%language%|%source%|%resolution%)',
		'VKI' => 'VKI', // Variable Keyframe Intervals
		'VR' => 'VR', // Virtual reality
		'VR180' => 'VR180',
		'Workprint' => [ 'WORKPRINT', 'WP' ],
		'Widescreen' => [ 'widescreen', 'WS' ], // Widescreen
		'x64' => 'x64', // software
		'x86' => 'x86', // software
		'XSCale' => 'Xscale', // software
		'XXX' => 'XXX'
	];

	// Format: DE + EN + NL / FR / IT / ES
	const MONTHS = [
		1 => 'Januar[iy]?|Janvier|Gennaio|Enero|Jan',
		2 => 'Februar[iy]?|Fevrier|Febbraio|Febrero|Feb',
		3 => 'Maerz|March|Moart|Mars|Marzo|Mar',
		4 => 'A[bpv]rile?|Apr',
		5 => 'M[ae][iy]|Maggio|Mayo',
		6 => 'Jun[ie]o?|Juin|Giugno|Jun',
		7 => 'Jul[iy]o?|Juillet|Luglio|Jul',
		8 => 'August|Aout|Agosto|Augustus|Aug',
		9 => 'Septemb[er][er]|Settembre|Septiembre|Sep',
		10 => 'O[ck]tob[er][er]|Ottobre|Octubre|Oct',
		11 => 'Novi?emb[er][er]|Nov',
		12 => 'D[ei][cz]i?emb[er][er]|Dec'
	];

	// Put together some flag/format arrays for better type parsing.
	// Flags
	const FLAGS_MOVIE = [ 'Dubbed', 'AC3 Dubbed' , 'HDR', 'HDR10', 'HDR10+', 'IMAX', 'Line dubbed', 'Micro dubbed', 'THEATRICAL', 'UNCUT', 'Remux', 'Subbed', 'Directors Cut' ];
	const FLAGS_EBOOK = [ 'eBook', 'Magazine', 'Comic', 'ePUB' ];
	const FLAGS_MUSIC = [ 'OST' ];
	const FLAGS_APPS = [ 'Cracked', 'Regged', 'KEYGEN', 'Incl. Patch', 'Crackfix', 'ISO', 'ARM', 'Intel', 'x86', 'x64', 'Portable' ];
	const FLAGS_ANIME = [ 'Anime', 'OVA', 'ONA', 'OAD' ];
	const FLAGS_XXX = [ 'XXX', 'JAV', 'Imageset' ];
	// Formats
	const FORMATS_VIDEO = [ 'AVC', 'VCD', 'SVCD', 'CVCD', 'XViD', 'DiVX', 'x264', 'x265', 'h264', 'h265', 'HEVC', 'MP4', 'MPEG', 'MPEG2', 'VC1', 'WMV' ];
	const FORMATS_MUSIC = [ 'FLAC', 'KONTAKT', 'MP3', 'OGG', 'WAV' ];
	const FORMATS_MVID = [ 'MBluray', 'MDVDR', 'MViD' ];
	// Sources
	const SOURCES_TV = [ 'ATVP', 'DSR', 'EDTV', 'HDTV', 'PDTV', 'SDTV', 'UHDTV' ];
	const SOURCES_GAMES = [ 'Console DVD', 'Nintendo eShop', 'XBLA' ];
}

/**
 * Polyfill functions.
 */

// PHP < 7.3
if ( !\function_exists( 'array_key_first' ) )
{
	function array_key_first( array $arr )
	{
		foreach( $arr as $key => $unused )
		{
			return $key;
		}
		return \null;
	}
}

if ( !\function_exists( 'array_key_last' ) )
{
	function array_key_last( array $array )
	{
		if ( !\is_array( $array ) || empty( $array ) )
		{
			return \null;
		}
		return \array_keys( $array )[ \count( $array ) - 1 ];
	}
}

// PHP < 8
if ( !\function_exists( 'str_contains' ) )
{
	function str_contains( string $haystack, string $needle )
	{
		return empty( $needle ) || \strpos( $haystack, $needle ) !== false;
	}
}
