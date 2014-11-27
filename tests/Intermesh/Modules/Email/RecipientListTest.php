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
		$str = 'Frank Schaap <frank.schaap@jmwhorecapersoneel.nl>, Frank <frank@vanekert.com>, "Radboud Verberne - radboudverberne.nl" <r.verberne@radboudverberne.nl>, Marleen Janssen <marleenjanssen_@hotmail.com>, Merijn Schering <mschering@intermesh.nl>, Linda <linda@intermesh.nl>, =?ISO-8859-1?Q?marleenjanssen=5F=40hotmail=2Ecom_=3Cmarleenjanssen=5F=40hotmail=2Ecom=3E=2C_m?==?ISO-8859-1?Q?schering=40intermesh=2Enl_=3Cmschering=40intermesh=2Enl=3E=2C_linda=40interm?==?ISO-8859-1?Q?esh=2Enl_=3Clinda=40intermesh=2Enl=3E=2C_sgommers=40hotmail=2Ecom_=3Csgommers=40?==?ISO-8859-1?Q?hotmail=2Ecom=3E=2C_Frank=40vanekert=2Ecom_=3CFrank=40vanekert=2Ecom=3E=2C_frank?==?ISO-8859-1?Q?=5Fschaap=40hotmail=2Ecom_=3Cfrank=5Fschaap=40hotmail=2Ecom=3E=2C_schmitzlotje?==?ISO-8859-1?Q?=40hotmail=2Ecom?= <schmitzlotje@hotmail.com>, maurice@salesday.nl';
//		$str = 'los@email.nl,nogeen@naam.nl, "Met naam" <mer@naam.nl>, "Comma , in naam" <comma@naam.nl>, ongeldig, <ongeldig2>';
		$list = new \Intermesh\Modules\Email\Util\RecipientList($str);
		
		var_dump($list);
		
		
	}
}
