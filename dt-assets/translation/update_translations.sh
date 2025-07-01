# This script generates the .pot file, uploads it to poeditor and downloads the .po and .mo files for each language
# Pass in script: ./update_translations.sh -t 'token'

APP_ID='251019';
POT_FILE_NAME="disciple_tools.pot"
DOMAIN="disciple_tools"

while getopts t: option
do
case "${option}"
in
t) TOKEN=${OPTARG};;
esac
done

if [ ! $TOKEN ]
 then echo "please enter poeditor token";
 exit 1
fi

# create new .pot file
wp i18n make-pot ../.. $POT_FILE_NAME --domain="disciple_tools" --skip-audit --exclude="tests,node_modules,vendor,dt-core/libraries,dt-core/dependencies,dt-core/config-p2p.php,dt-core/admin/menu,dt-core/admin/menu,*.js,dt-core/admin/site-link-post-type.php,dt-mapping/mapping-admin.php,dt-core/admin/multi-role,
dt-core/admin,template-blank*,*.css"

#Commit changes
git add *.pot;
git add *.po;
git add *.mo;
#git commit -m "Update Translations";
# git push origin master?
