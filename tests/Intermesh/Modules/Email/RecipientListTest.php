<?php

namespace Intermesh\Core\Db;

use PHPUnit_Framework_TestCase;

/**
 * The App class is a collection of static functions to access common services
 * like the configuration, reqeuest, debugger etc.
 */
class RecipientListTest extends PHPUnit_Framework_TestCase {

	public function testTimezone() {
		//$str = 'tc@tvoosterplas.nl,jbjmstaats@home.nl,wjvanengelen@online.nl,wedijkstra@hotmail.n,info@badentegeldesign.nl,cathrienverhoeven@hotmail.com,claudiadelaat7@googlemail.com,denishadza@hotmail.com,d.heij1@kpnplanet.nl,erik.bevers@zonnet.nl,gerbenpel@msn.com,gijsvolders@hotmail.com,i_v_dorssen@hotmail.com,janpieterdekoning@hotmail.com,kimberlyklerx@gmail.com,maikel.moors@home.nl,dhroomen@gmail.com,marieke.l@hotmail.com,mathy.cordang@gmail.com,mschering@intermesh.nl,michel.vanderelst@emc.com,nico.karin@ziggo.nl,noravaal@hotmail.com,raymond.beaard@tele2.nl,horssen48@hotmail.com,"jn.bonants@avans.nl." <basbruurmijn@hotmail.com>,delaat.maarten@gmail.com,wjvanengelen@hotmail.nl';
		$str = 'Adri Schauwaert <adri@schauwaert.com>, Cees en Toos <ceesentoos.heijne@home.nl>, Jitske Schauwaert <jitskeschauwaert@hotmail.com>, johan moerland <johan1@telfort.nl>, "joosen@notarisgreving.nl" <joosen@notarisgreving.nl>, Jorren schauwaert <jorrenschauwaert@gmail.com>, Jouke schauwaert <joukeschauwaert@hotmail.com>, Karin Schauwaert <karin@schauwaert.com>, Kim Heijne <kimheijne@hotmail.com>, Marco Mensen <marcomensen@kpnmail.nl>, Marinus <mschering@intermesh.nl>, Michel Canjels <michelcanjels@casema.nl>, papa <cees.heijne@mw-brabant.politie.nl>, pascal gladjes <vangend@versatel.nl>, =?ISO-8859-1?Q?Peter_G=F6tenstedt?= <pgotenstedt@gmail.com>, Richard Mouwen <mj.mouwen@meeus.com>, Ronald Brondsema <r.brondsema@tiscali.nl>, ronald h <ronald@musicminded.com>, Sannyboy Williamson <sanderthijs@gmail.com>, Wendy Heijne <heijne.wendy@gmail.com>, Marnix Hendriks <mjmhendriks2010@hotmail.com>, "Fam. Mensen" <N.Mensen-Geul@kpnmail.nl>, annemarie.v.es@online.nl, gheyn@telfort.nl, jackjoosen@ziggo.nl, jokejoosen@gmail.com, kyrajoosen@hotmail.com, c.kneijber@upcmail.nl, karina.kneijber@gmail.com, cjjm.leijten@chello.nl, leijt046@concepts.nl, rietleijten@hotmail.com, pleijten@fastmail.fm, peter_leijten@goalistic.nl, rpm.leijten@worldonline.nl, ruud@projectprofession.nl, p.perenboom@upcmail.nl, marlie.scheepers@hotmail.com, vanloen@xs4all.nl, Arie van der Windt <windtjes@home.nl>, joost van den Hurk <joostvdhurk@hotmail.com>, "Ronald,Cora,Noa.Julia" <ronald.k@versatel.nl>, Serge en linda Knook <knook@scarlet.nl>';
//		$str = 'los@email.nl,nogeen@naam.nl, "Met naam" <mer@naam.nl>, "Comma , in naam" <comma@naam.nl>, ongeldig, <ongeldig2>';
		$list = new \Intermesh\Modules\Email\Util\RecipientList($str);
		
		var_dump($list);
		
		
	}
}
