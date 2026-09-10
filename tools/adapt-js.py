from pathlib import Path
import re
root=Path(__file__).resolve().parents[1];src=root.parent/'reference/ASFAR final3/assets/js';dest=root/'asfar/assets/js'
s=(src/'app.js').read_text()
start=s.index("  form.addEventListener('submit'")
end=s.index("  form.addEventListener('input'",start)
s=s[:start]+s[end:]
s=s.replace("  var TO = 'info@asfar.com.sa';",'')
s=s.replace("|| 'Close menu'","|| ''").replace("|| 'Open menu'","|| ''").replace("|| 'Photos'","|| ''").replace("|| 'Morph'","|| ''")
s=s.replace("document.documentElement.lang === 'ar' ? 'إغلاق' : 'Close'",'asfarSettings.close')
s=re.sub(r'var COPY = rtl\s*\? \{.*?\}\s*: \{.*?\};','var COPY = asfarSettings;',s,flags=re.S)
s=s.replace("ph.innerHTML = '<img src=\"assets/img/team/' + photo + '\" alt=\"\" />';", "ph.replaceChildren(); var portrait = document.createElement('img'); portrait.src = photo; portrait.alt = ''; ph.appendChild(portrait);")
s=re.sub(r"'assets/(img/[^']*)'",lambda m:"asfarSettings.assets + '"+m[1]+"'",s)
# Profile markup receives labels as DOM properties, never interpolated HTML.
s=s.replace("'<button type=\"button\" class=\"mexp__close\" aria-label=\"' + COPY.close + '\">×</button>'", "'<button type=\"button\" class=\"mexp__close\">×</button>'")
s=s.replace("veil = document.createElement('div');", "panel.querySelector('.mexp__close').setAttribute('aria-label', COPY.close);\n      veil = document.createElement('div');")
# WordPress routes do not use HTML filenames. Preserve page-cover transitions for local news/team links.
s=s.replace("/news.*\\.html/", "/news/")
(dest/'app.js').write_text(s)
s=(src/'deck.js').read_text()
start=s.index('  /* ---------- investment map')
end=s.index('  /* ---------- sector strip',start)
s=s[:start]+s[end:]
start=s.index('  /* ---------- team tabs')
end=s.index('  /* ----------',start+20)
s=s[:start]+s[end:]
(dest/'deck.js').write_text(s)
