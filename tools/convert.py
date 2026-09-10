from pathlib import Path
from bs4 import BeautifulSoup, NavigableString, Comment, Tag
import json, re, html, shutil
ROOT=Path(__file__).resolve().parents[1]; T=ROOT/'asfar'; SRC=ROOT.parent/'reference/ASFAR final3'
seed={'pages':{},'options':{}}; groups={}; counter=0

def q(s): return "'"+str(s).replace('\\','\\\\').replace("'","\\'")+"'"
def field(scope,label,value,typ='text'):
 global counter
 counter+=1; name='c_'+str(counter); key='field_asfar_'+name
 f={'key':key,'name':name,'label':label[:100],'type':typ}
 if typ in ['image','file']: f.update(return_format='id', library='all'); value={'asset':value}
 if typ=='textarea': f.update(rows=3,new_lines='')
 if typ=='url': f['type']='link';f['return_format']='array'; value={'url':value,'title':'','target':''}
 groups.setdefault(scope,[]).append(f)
 target=seed['options'].setdefault(scope,{}) if scope.startswith('global_') else seed['pages'][scope]['values']
 target[name]=value
 return name

def expr(scope,name,typ='text'):
 ctx=q(scope) if scope.startswith('global_') else 'get_the_ID()'
 fn='asfar_media' if typ in ['image','file'] else 'asfar_link' if typ=='url' else 'asfar_value'
 return f'{fn}({q(name)}, {ctx})'
def output(scope,label,value,typ='text',attr=False):
 name=field(scope,label,value,typ); e=expr(scope,name,typ)
 return '<?php echo '+('esc_url' if typ in ['url','image','file'] else 'esc_attr' if attr else 'esc_html')+'( '+e+' ); ?>'

void={'area','base','br','col','embed','hr','img','input','link','meta','param','source','track','wbr'}
copyattrs={'alt','title','aria-label','placeholder','data-label-open','data-label-close','data-label-on','data-label-off','data-msg-invalid','data-msg-sent','data-name','data-role','data-bio'}
def compile_node(n,scope,section='Content'):
 if isinstance(n,Comment): return ''
 if isinstance(n,NavigableString):
  s=str(n)
  if not s.strip(): return s
  return output(scope,section+' — '+s.strip()[:55],s,'textarea' if len(s)>110 else 'text')
 if not isinstance(n,Tag): return ''
 if n.name=='script': return ''
 if n.get('id'): section=n['id'].replace('-',' ').title()
 if n.name=='svg': return str(n) # Source artwork, not editable content.
 if 'nav__lang' in n.get('class',[]): return '<div class="nav__lang"><?php asfar_languages(); ?></div>'
 if n.get('id')=='dkMap': return '<?php asfar_portfolio(); ?>'
 if n.get('id')=='dkTeam':
  # Use original surrounding chrome, replace data-driven rail with PHP.
  for el in n.select('script'): el.decompose()
  rail=n.select_one('.dk-team__rail'); rail.clear(); rail.append(BeautifulSoup('<asfar-team></asfar-team>','html.parser'))
 if n.name=='asfar-team': return '<?php asfar_team_home(); ?>'
 if n.name=='form':
  n['method']='post'; n['action']='__FORM__'
 attrs=[]
 for k,v in n.attrs.items():
  if isinstance(v,list): v=' '.join(v)
  if v is None: attrs.append(k);continue
  if k=='action' and v=='__FORM__': val='<?php echo esc_url( admin_url( "admin-post.php" ) ); ?>'
  elif k=='data-morphmarks':
   data=json.loads(v); seed['options'].setdefault('global_'+('ar' if scope.endswith('-ar') or scope.endswith('_ar') else 'en'),{})['hero_marks']=data
   val='<?php echo esc_attr( wp_json_encode( asfar_marks() ) ); ?>'
  elif k=='data-photo': val=output(scope,section+' portrait','assets/img/team/'+v if v else '', 'image',True)
  elif k in ['src','poster','data-video'] and v.startswith('assets/'):
   if 'nav__logo-img--light' in n.get('class',[]) or 'loader__logo' in n.get('class',[]): val='<?php echo esc_url( asfar_logo() ); ?>'
   elif 'dk-footer__logo' in n.get('class',[]): val='<?php echo esc_url( asfar_media( "footer_logo", asfar_options_id() ) ); ?>'
   else: val=output(scope,section+' '+k,v,'file' if k=='data-video' or n.name=='source' else 'image',True)
  elif k=='href' and not v.startswith('data:'): val=output(scope,section+' link',v,'url',True)
  elif k in copyattrs: val=output(scope,section+' '+k,v,'textarea' if k=='data-bio' else 'text',True)
  elif k=='style' and 'url(' in v:
   val=re.sub(r'url\([\'"]?(assets/[^\)\'\"]+)[\'"]?\)',lambda m:'url('+output(scope,section+' background',m[1],'image',True)+')',v)
  else: val=html.escape(str(v),quote=True)
  attrs.append(k+'="'+val+'"')
 start='<'+n.name+(' '+' '.join(attrs) if attrs else '')+'>'
 if n.name in void:return start
 content=''.join(compile_node(c,scope,section) for c in list(n.children))
 if n.name=='form': content='<?php asfar_form_hidden(); ?>'+content
 return start+content+'</'+n.name+'>'

for path in sorted(SRC.glob('*.html')):
 slug=path.stem; soup=BeautifulSoup(path.read_text(),'html.parser');lang='ar' if slug.endswith('-ar') else 'en'
 seed['pages'][slug]={'title':soup.title.get_text(),'lang':lang,'values':{},'type':'post' if slug.startswith('news-') and slug!='news-ar' else 'page'}
 body=soup.body
 main=body.find('main')
 footer=body.find('footer')
 # homepage source omits closing main; detach footer from it.
 if footer: footer.extract()
 for el in body.select('script[src]'):el.decompose()
 if slug in ['index','index-ar']:
  scope='global_'+lang
  headnodes=[]
  for el in list(body.children):
   if el is main: break
   headnodes.append(el)
  (T/'template-parts'/('header-'+lang+'.php')).write_text(''.join(compile_node(el,scope,'Header') for el in headnodes))
  (T/'template-parts'/('footer-'+lang+'.php')).write_text(compile_node(footer,scope,'Footer'))
 if main:
  (T/'template-parts/pages'/(slug+'.php')).write_text(compile_node(main,slug))
 else: print('NO MAIN',slug)
# Structured global content.
for lang in ['en','ar']:
 opt=seed['options']['global_'+lang]
 opt['footer_logo']={'asset':'assets/img/asfar-footer-logo.svg'}
 opt.update({'close':'إغلاق' if lang=='ar' else 'Close','bio_empty':'السيرة الذاتية ستتوفر قريباً.' if lang=='ar' else 'Biography to follow.','bio_label':'نبذة عن العضو' if lang=='ar' else 'Member profile','form_success':'شكرًا لك. تم استلام رسالتك.' if lang=='ar' else 'Thank you. Your message has been received.','form_error':'تعذر إرسال الرسالة. يرجى المحاولة مرة أخرى.' if lang=='ar' else 'Your message could not be sent. Please try again.','form_invalid':'يرجى إكمال جميع الحقول ببريد إلكتروني صالح.' if lang=='ar' else 'Please complete all fields with a valid email.','form_subject':'رسالة جديدة من الموقع' if lang=='ar' else 'New website enquiry','not_found':'الصفحة غير موجودة' if lang=='ar' else 'Page not found','back_home':'العودة إلى الرئيسية' if lang=='ar' else 'Return home','archive_title':'الأخبار' if lang=='ar' else 'News','notification_email':'','contact_email':'info@asfar.com.sa'})
 team=json.loads((SRC/'assets/data/team.json').read_text())
 opt['team']=[{'group':m['group'],'name':m['name_'+lang],'role':m['role_'+lang],'bio':m['bio_'+lang],'photo':{'asset':'assets/img/team/'+m['photo']} if m['photo'] else 0} for m in team]
 s=BeautifulSoup((SRC/('index-ar.html' if lang=='ar' else 'index.html')).read_text(),'html.parser')
 data=json.loads(s.find(id='dkMapData').string)
 for m in data:
  m['bg']={'asset':m['bg']};m['img']={'asset':m['img']};m['mark_x']=m['mark'][0] if m['mark'] else 0;m['mark_y']=m['mark'][1] if m['mark'] else 0;del m['mark']
  m['stats']=[{'value':re.sub('<[^>]+>','',v).replace('M2','M²'),'label':l} for v,l in m['stats']]
 opt['portfolio']=data
 opt['portfolio_title']='محفظة أسفار الاستثمارية' if lang=='ar' else 'ASFAR Investment Portfolio'
(T/'inc/seed.json').write_text(json.dumps(seed,ensure_ascii=False,indent=2))
(T/'inc/fields.json').write_text(json.dumps(groups,ensure_ascii=False,indent=2))
# Content files are import sources only, never shipped as browser assets.
shutil.rmtree(T/'assets/data') if (T/'assets/data').exists() else None
print('Compiled',len(seed['pages']),'pages and',counter,'typed content fields.')
