from pathlib import Path
import re,json,html,os
root=Path(__file__).resolve().parents[1]; reference=Path(os.environ.get('ASFAR_REFERENCE_DIR', root.parent/'reference/AMV4')); dest=Path(os.environ.get('ASFAR_PREVIEW_DIR','/private/tmp/amv4-preview'));dest.mkdir(exist_ok=True)
for name,target in [('theme',root/'asfar'),('media',root/'media/asfar-media')]:
 p=dest/name
 if not p.exists():p.symlink_to(target,target_is_directory=True)
for lang,file in [('en','index-en.html'),('ar','index.html')]:
 s=(reference/file).read_text()
 marks=[{'name':n,'cta':'','href':'#projects'} for n in (['The Mountains','The Highlands','The Oasis','The Coast'] if lang=='en' else ['الجبال','المرتفعات','الواحة','الساحل'])]
 s=s.replace('id="heroSlider"', 'id="heroSlider" data-morphmarks="'+html.escape(json.dumps(marks),quote=True)+'"')
 s=re.sub(r'class="([^"]+)"',lambda m:'class="'+' '.join('mt-'+c for c in m[1].split())+'"',s)
 s=re.sub(r'<script\b[^>]*src=[^>]*>.*?</script>','',s,flags=re.S)
 s=s.replace('assets/img/','/media/img/').replace('assets/video/','/theme/assets/video/').replace('assets/css/','/theme/assets/css/')
 s=s.replace('</head>','<link rel="stylesheet" href="/theme/style.css"></head>')
 # Match the server-rendered WordPress portfolio panes and inline SVG.
 match=re.search(r'<script[^>]*id="dkMapData"[^>]*>(.*?)</script>',s,re.S);data=json.loads(match[1])
 svg=(root/'media/asfar-media/img/asfar-map-deck.svg').read_text();svg=re.sub(r'class="([^"]+)"',lambda m:'class="'+' '.join('mt-'+c for c in m[1].split())+'"',svg)
 s=s.replace('<div class="mt-dk-map__stage mt-dk-rise" aria-hidden="true">','<div class="mt-dk-map__stage mt-dk-rise">'+svg)
 # The map source includes one placeholder panel; prepend native-style data panes.
 panes=''
 for i,d in enumerate(data):
  panes+='<div class="mt-asfar-portfolio-pane" '+('hidden' if i else '')+' data-hot="'+d['hot']+'" data-x="'+str((d['mark'] or [0,0])[0])+'" data-y="'+str((d['mark'] or [0,0])[1])+'" data-label="'+d['label']+'"><h3>'+d['name']+'</h3></div>'
 s=s.replace('<aside class="mt-dk-map__panel mt-dk-rise" aria-live="polite">','<aside class="mt-dk-map__panel mt-dk-rise" aria-live="polite">'+panes)
 s=s.replace('id="contactForm"', 'id="sourceContactForm"')
 s+='''<script>var asfarSettings={assets:'/media/',close:'Close',empty:'',label:'Biography',endpoint:'',error:'',invalid:''};</script><script src="/theme/assets/js/vendor/lenis.min.js"></script><script src="/theme/assets/js/app.js"></script><script src="/theme/assets/js/deck.js"></script><script src="/theme/assets/js/wordpress.js"></script>'''
 (dest/(lang+'.html')).write_text(s)
print(dest)
