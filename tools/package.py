"""Validate and package the theme. Usage: python3 tools/package.py [v1.0.0]."""
from pathlib import Path
import re, subprocess, sys, zipfile, json, hashlib
root=Path(__file__).resolve().parents[1]; theme=root/'asfar'
version=re.search(r'^Version:\s*(\S+)',(theme/'style.css').read_text(),re.M)[1]
if len(sys.argv)>1 and sys.argv[1].removeprefix('v')!=version:raise SystemExit('Release tag must match style.css Version')
bootstrap=re.search(r"define\( 'ASFAR_VERSION', '([^']+)'",(theme/'functions.php').read_text())[1]
if bootstrap!=version:raise SystemExit('functions.php and style.css versions must match')
for name in ['style.css','functions.php','index.php','header.php','footer.php','templates/template-home.php','page.php','single.php','archive.php','404.php','screenshot.png']:
 if not (theme/name).is_file():raise SystemExit('Missing '+name)
for name in ['front-page.php','home.php']:
 if (theme/name).exists():raise SystemExit('Forbidden homepage template: '+name)
for path in theme.rglob('*.php'):
 result=subprocess.run(['php','-l',str(path)],capture_output=True,text=True)
 if result.returncode:raise SystemExit(result.stdout+result.stderr)
for path in (theme/'assets/js').glob('*.js'):
 subprocess.run(['node','--check',str(path)],check=True)
(root/'dist').mkdir(exist_ok=True)
with zipfile.ZipFile(root/'dist/asfar.zip','w',zipfile.ZIP_DEFLATED) as z:
 for p in sorted(theme.rglob('*')):
  if p.is_file() and not p.name.startswith('.'):z.write(p,p.relative_to(root))
with zipfile.ZipFile(root/'dist/asfar.zip') as z:
 assert 'asfar/style.css' in z.namelist()
 assert all(n.startswith('asfar/') for n in z.namelist())
 assert z.testzip() is None
print(f'Created dist/asfar.zip — version {version}, {(root/"dist/asfar.zip").stat().st_size:,} bytes')

images = {'.png', '.jpg', '.jpeg', '.webp', '.gif', '.svg', '.avif', '.ico'}
assert not any(p.suffix.lower() in images for p in theme.rglob('*') if p.is_file() and p != theme / 'screenshot.png'), 'Only the WordPress theme preview screenshot may be bundled'
media = root / 'media/asfar-media'
manifest = json.loads((theme/'inc/media-manifest.json').read_text())
with zipfile.ZipFile(root/'dist/asfar-media.zip','w',zipfile.ZIP_DEFLATED) as z:
 for relative, expected in manifest.items():
  p = media / relative
  assert hashlib.sha256(p.read_bytes()).hexdigest() == expected, relative
  z.write(p, 'asfar-media/' + relative)
print(f'Created dist/asfar-media.zip — {len(manifest)} images')
