# Projet php


- **REMILI Rédouane**
- **KRIFAH Amel**  
- **COBLENTZ Robin**

##

### Sources & lien utiles :
- <https://symfony.com/index.php/doc/current/security.html>
- <https://openclassrooms.com/fr/courses/5489656-construisez-un-site-web-a-l-aide-du-framework-symfony-5/5654131-securisez-lacces-de-votre-site-web>
- <https://www.youtube.com/watch?v=NjF-gF1yNqo&ab_channel=LiorCHAMLA>
- <https://www.php.net/manual/fr/function.password-hash.php>

#
Installer flex : outils pour gérer l’installation et la configuration des librairie 

`composer require symfony/flex`
flex permet de configurer alors que composer permet seulement l’installation

**Pour mettre en place le système d’authentification et système d’autorisation (permet de limiter les accès à certaine ressources) de connexion, on installe un bundle Symfony :**
`composer require symfony/security-bundle`

# -> Chiffrement des mots de passe & Autorisations d'accès
### --> On va dans config/package/security.yaml qui vient d’être créé :

Ligne 4-5 c’est l’encodage :

```
password_hashers: 
Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface:'auto'
```
Rajouter juste à la suite l'algorithme d'encodage de l'user :
```
App\Entity\User:
        algorithm: auto
```

A partir ligne 7 rajouter :  
```
app_user_provider:
        entity:
            class: App\Entity\User
            property: email
```
Le provider c’est l’entité qui va gérer l’authentification : Ici C’est l’entité USER


Puis ligne 17 remplacer 
`provider: users_in_memory`

Par
`provider: app_user_provider`
On dit ici au firewall de laisser rentrer le provider qui sera un utilisateur

Dans la methode indexController ajouter le paramètre :
```
UserPasswordHasherInterface $passwordHashed
```
Sans oublier sont namespace : 
```
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
```

On fini par ajouter la method hashPassword au ->setPassword de la fonction index :
```
->setPassword($passwordHasher->hashPassword($user, 'randompass'))
```
#### Mais il s'emble que l'injection de dependance ne marche pas : Il y a un msg d'erreur et le mdp n'est pas crypté
#### -> Donc on utilise une methode de cryptage php

```
public function index(EntityManager $em)
  {
    $user = new User();
    $passwordHashed = password_hash("randompass", PASSWORD_BCRYPT,  ['cost' => 12]);

    $user->setName("Red")
      ->setFirstName("John")
      ->setUsername("Bobby")
      ->setPassword($passwordHashed)
      ->setEmail("bob@bob.com")
      ->setRoles(['ROLE_USER'])
      ->setBirthDate(new DateTime);
```

# -> Structure d'un utilisateur (BDD, entité Doctrine) & Gestion des rôles
### ->	Dans l’entité USER rajouter un rôle :
```
/**
     * @ORM\Column(type="json")
     */
    private $roles = [];

  /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }
```

Ensuite aller dans l’entité USER et ligne 13 ou 14 rajouter
`implements UserInterface`

Et rajouter son namespace :
`use Symfony\Component\Security\Core\User\UserInterface;`

Tout en bas de Entity\User rajouter pour définir le contrat
```
/**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials() {

    }

    /**
     * Returns the identifier for this user (e.g. its username or email address).
     */
    public function getUserIdentifier(): string {
      return "";
    }
```


### -> Dans indexController rajouter les champs :
`->setRoles(['ROLE_USER'])`

Et modifier dateTime : POur que la date soie automatiquement celle d'aujourd'hui
`->setBirthDate(new DateTime);`


### -> Pour vérifier l’état de la bdd par rapport au model : 
`php vendor/bin/doctrine orm:validate-schema`

Puis pour modifier et mettre à jours la bdd par rapport au model : 

`php vendor/bin/doctrine orm:schema-tool:update --force`

#### Voila donc l'attribut role qui s'ajoute à USER et le mdp encodé
![Texte alternatif](/imgReadme/capture.png "Attribut RoleUser").


# -> Système Inscription : 
### -> N’aura pas marché malgré les différents moyens utilisés


Pour faciliter la création du système d’authentification installer un bundle Symfony :
`composer req symfony/maker-bundle --dev`

Dans Router.php ligne 129 Pour dire qu’on applique uniquement la recherche (le routing) si le lien n’est pas null

```
if($class) 
      {
        $this->registerRoute($class);
      }
```

Security.yaml : Rajouter ligne 18
Pour vérifier la conformité des infos de connexion Utilisateurs de la base de données 
```
guard:
      authenticators:
        - App\Security\LoginFormAuthenticator
    logout:
      path: app_logout
```

En s'inspirant des projets Symfony rajouter :
-	Un fichier : SecurityController.php
- Un Fichier src\Security\ LoginFormAuthenticator.php 
- Un Fichier templates\security\ login.html.twig
- Un fichier templates\registration\ register.html.twig

##### Mais ça n'a pas marché :-(

