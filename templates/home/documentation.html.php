<h2>JWT Documentation</h2>

<h3>Utilisation</h3>

<p>Le JWT_SECRET est à changer dans le .env</p><br>
<p>
    Une fois le secret changer, il faut pouvoir enregistrer un user
    une fois que l'utilisateur est enregistré en base de donnée
    il faut préparer un LoginController/AuthController et un LoginType ainsi qu'un template.
</p>
<br>
<p>
    C'est dans le LoginController que la génération du token vas se faire en utilisant les données
    de l'utilisateur.
    Pour générer un token on utilise le JWTService dans Core\Service.
    Dans le service il nous faut d'abord récupérer le secret dans le .env.
    Pour que notre token soit valide il nous faut créer une date de création (correspond à la date de la création du token lors du login)et d'expiration pour
    savoir si oui ou non le token est valide.
    Le Header et le payload sont crypté en bas64, il faut remplacer certains charactères qui ne sont pas accepté dans les JWT
    On encode aussi le secret en bas64
    On génére une signature pour signer la chaine header, payload et secret.
    Il faut aussi remplacer les characrères pour la signature.
    Ensuite on crée le token avec ce qui a été encodé.
</p>
<br>
<p>

    Dans le service il y a aussi des méthodes pour vérifier notre token.
    check() = créer un token identique juste on enlève la date car sinon jamais bon et le compare si c'est bon retourne le token bon
    2 autres méthodes pour récupérer le header et le payload
    Une autre pour vérifier si il n'est pas expiré
    Et une autre pour vérifier si il n'y a pas de mauvais charactères
</p><br>

<p>
    Pour générer un token dans le controller il faut d'abord récupérer l'email de l'utilisateur et vérifier que le mdp match.
    Ensuite il faut préciser le header et le payload avec les données que l'on veut ajouter au toke.
    Enfin on appel la method generate() en lui passant le header, payload et secret. Et ensuite on met le token dans un cookie
    Pour supprimer le cookie il suffit juste de mettre une date passée.
</p>

<br>
<p>
    Pour vérifier que l'utilisateur à bien son token on utilise le JWTVerifService.
    On récupère l'header Authorization ou le cookie et on vérifie si le token est présent en couvrant toutes les éventualités
    Et on vérifie si le token est valide.
</p>
<br>
<p>
    ce service permet de voir si le token est présent et si il passe tous les tests de validité.
    Pour sécuriser une route il suffit juste d'appeler la méthode checkToken() du service.
</p>