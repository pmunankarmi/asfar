from pathlib import Path
from bs4 import BeautifulSoup
import re,json
r=Path(__file__).resolve().parents[1]; t=r/'asfar'; src=r.parent/'reference/ASFAR final3'; seed=json.loads((t/'inc/seed.json').read_text())
def inner(s,cls,replacement,occ=0):
 matches=list(re.finditer(r'<(div|nav)[^>]*class="'+re.escape(cls)+r'"[^>]*>',s));m=matches[occ];start=m.end(); tag=m[1];depth=1
 for n in re.finditer(r'</?'+tag+r'\b[^>]*>',s[start:]):
  depth+=-1 if n[0].startswith('</') else 1
  if depth==0:return s[:start]+replacement+s[start+n.start():]
 raise ValueError(cls)
for lang in ['en','ar']:
 slug='index-ar' if lang=='ar' else 'index';p=t/'template-parts/pages'/f'{slug}.php';s=p.read_text()
 s=inner(s,'dk-news__strip',"<?php asfar_news( true ); ?>")
 s=s.replace('id="dkTeam" hidden="" style="display:none"','id="dkTeam" <?php echo asfar_option( "show_team" ) ? "" : "hidden"; ?>')
 # Repeater lists keep layout markup in PHP.
 s=inner(s,'dk-faq__list dk-rise',"<?php asfar_faq(); ?>")
 s=inner(s,'dk-partners__card dk-rise',"<?php asfar_partners(); ?>")
 s=inner(s,'dk-sectors__track',"<?php asfar_sectors(); ?>")
 p.write_text(s)
 p=t/'template-parts/pages'/('news-ar.php' if lang=='ar' else 'news.php');p.write_text(inner(p.read_text(),'newspage__grid',"<?php asfar_news( false ); ?>"))
 p=t/'template-parts/pages'/('team-ar.php' if lang=='ar' else 'team.php');s=p.read_text()
 for i,group in enumerate(['board','committees','leadership']):s=inner(s,'teampage__grid',"<?php asfar_team_page( '"+group+"' ); ?>",i)
 p.write_text(s)
 soup=BeautifulSoup((src/(slug+'.html')).read_text(),'html.parser');opt=seed['options']['global_'+lang]
 opt['show_team']=1
 opt['faq']=[{'question':e.select_one('.dk-faq__btn span').get_text(),'answer':e.select_one('.dk-faq__a').get_text()} for e in soup.select('.dk-faq__item')]
 opt['partners']=[{'image':{'asset':e['src']},'name':e.get('alt',''),'url':''} for e in soup.select('.dk-partners__card img')]
 opt['sectors']=[{'image':{'asset':e.select_one('img')['src']},'label':e.get_text(strip=True)} for e in soup.select('.dk-sectors__track > *')]
 p=t/'template-parts'/('footer-'+lang+'.php');s=p.read_text()
 for k,v in opt.items():
  if v=='info@asfar.com.sa':s=s.replace("asfar_value('"+k+"', 'global_"+lang+"')","asfar_option( 'contact_email' )")
  if isinstance(v,dict) and v.get('url')=='mailto:info@asfar.com.sa':s=s.replace("asfar_link('"+k+"', 'global_"+lang+"')","'mailto:' . asfar_option( 'contact_email' )")
 s=s.replace('</footer>',"<?php asfar_social(); ?></footer>");p.write_text(s)
# Native news collection, including the external article.
news=json.loads((src/'assets/data/news.json').read_text());seed['news']=news
(t/'inc/seed.json').write_text(json.dumps(seed,ensure_ascii=False,indent=2))
