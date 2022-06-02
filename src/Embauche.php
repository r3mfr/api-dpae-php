<?php


namespace R3m\Dpae;

use R3m\Dpae\ApiOperations\All;
use R3m\Dpae\ApiOperations\One;
use R3m\Dpae\ApiOperations\Retrieve;

/**
 * Embauche
 *
 * @property $id string Identifiant unique de l'embauche
 * @property $dpae ApiResource Dpae liéé à l'embauchee
 * @property $idExterne string Identifiant dans le système externe
 * @property $raisonSociale string Nom ou raison sociale de l'employeur. Obligatoire, 64 caractères, [A-Z0-9&- ]
 * @property $siret string N° SIRET de l'établissement employeur. Obligatoire, 14 chiffres
 * @property $nomAbonneUrssaf string Nom de l'abonné aux services en ligne de l'URSSAF. Obligatoire, 32 caractères max., [A-Z&- .]
 * @property $prenomAbonneUrssaf string Prénom de l'abonné aux services en ligne de l'URSSAF. Obligatoire, 32 caractères max., [A-Z&- .]
 * @property $codeUrssaf string Code de l'URSSAF réceptrice des données. Obligatoire, 3 caractères. Liste des Codes URSSAF : https://www2.due.urssaf.fr/declarant/jasperServlet
 * @property $adresse1 string Adresse de l'établissement (1ere ligne). Obligatoire, 32 caractères max., [A-Z0-9&- .,']
 * @property $adresse2 string Adresse de l'établissement (2e ligne). 32 caractères max, [A-Z0-9&- .,']
 * @property $codePostal string Code postal de l'établissement. Obligatoire, 5 chiffres
 * @property $ville string Ville de l'établissement. Obligatoire, 27 caractères max., [A-Z0-9&- .,']
 * @property $telephone string Numéro de téléphone de l'employeur. 11 chiffres max.
 * @property $codeNaf string Code NAF de l'employeur (nouvelle nomenclature 2008). Obligatoire, 5 caractères
 * @property $codeCentreMedecineTravail string Code centre de médecine du travail. Obligatoire (sauf pour contrat CTT), 10 caractères max. Liste des Codes Service santé (Code SST) : https://www2.due.urssaf.fr/declarant/jasperServlet. Si le centre de médecine est interne à l'établissement, renseignez MT999.
 * @property $salarieNom string Nom du salarié. Obligatoire, 32 caractères max., [A-Z&- .]
 * @property $salarieNomEpoux string Nom d'époux du salarié. 32 caractères max., [A-Z&- .]
 * @property $salariePrenom string Prénom du salarié. Obligatoire, 32 caractères max., [A-Z -&.]
 * @property $salarieNumeroSecu string Numéro de sécurité sociale. 13 chiffres
 * @property $salarieSexe string Sexe du salarié (M/F). Obligatoire, 1 caractère M ou F
 * @property $salarieDateNaissance string Date de naissance (format JJMMAAAA). Obligatoire, 8 chiffres
 * @property $salarieLieuNaissance string Lieu de naissance du salarié (commune ou pays). Obligatoire, 24 caractères max., [A-Z0-9&- .,']
 * @property $salarieDepartementNaissance string Code du département de naissance du salarié. 2 caractères, [A-Z0-9]. Si né hors de France, renseignez 99. Si né dans un DROM/COM, renseignez les deux premiers chiffres du numéro du DROM/COM (soit 97 ou 98).
 * @property $dateEmbauche string Date de l'embauche (format JJMMAAAA). Obligatoire, 8 chiffres
 * @property $heureEmbauche  string Heure de l'embauche (format HHMM). Obligatoire, 4 chiffres
 * @property $typeContrat string Code type de contrat (1, 2, 3) pour CDD, CDI, CTT. Obligatoire, 1 chiffre
 * @property $dateFinCDD string Date de fin de CDD (format JJMMAAAA). Obligatoire si typeContrat=1, 8 chiffres
 * @property $dureePeriodeEssai string Durée de la période d'essai, en jours. 3 chiffres max.
 *
 * @package R3m\Dpae
 */
class Embauche extends ApiResource implements \JsonSerializable
{

    use All;
    use One;
    use Retrieve;
    use ApiOperations\Save {
        save as traitSave;
    }

    protected static $OBJECT_NAME = 'embauche';

    public function save()
    {
        if (array_key_exists('id', $this->values)) {
            throw new Exception\BadMethodCallException('La mise à jour d\'une embauche existante n\'est pas permise.');
        }

        $this->traitSave();
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id ?? '';
    }

    /**
     * @param ApiResource $dpae
     * @return Embauche
     */
    public function setDpae(ApiResource $dpae): Embauche
    {
        $this->dpae = $dpae;
        return $this;
    }

    /**
     * @return string
     */
    public function getIdExterne(): string
    {
        return $this->idExterne ?? '';
    }

    /**
     * @param string $idExterne
     * @return Embauche
     */
    public function setIdExterne(string $idExterne): Embauche
    {
        $this->idExterne = $idExterne;
        return $this;
    }

    /**
     * @return string
     */
    public function getRaisonSociale(): string
    {
        return $this->raisonSociale ?? '';
    }

    /**
     * @param string $raisonSociale
     * @return Embauche
     */
    public function setRaisonSociale(string $raisonSociale): Embauche
    {
        $this->raisonSociale = $raisonSociale;
        return $this;
    }

    /**
     * @return string
     */
    public function getSiret(): string
    {
        return $this->siret ?? '';
    }

    /**
     * @param string $siret
     * @return Embauche
     */
    public function setSiret(string $siret): Embauche
    {
        $this->siret = $siret;
        return $this;
    }

    /**
     * @return string
     */
    public function getNomAbonneUrssaf(): string
    {
        return $this->nomAbonneUrssaf ?? '';
    }

    /**
     * @param string $nomAbonneUrssaf
     * @return Embauche
     */
    public function setNomAbonneUrssaf(string $nomAbonneUrssaf): Embauche
    {
        $this->nomAbonneUrssaf = $nomAbonneUrssaf;
        return $this;
    }

    /**
     * @return string
     */
    public function getPrenomAbonneUrssaf(): string
    {
        return $this->prenomAbonneUrssaf ?? '';
    }

    /**
     * @param string $prenomAbonneUrssaf
     * @return Embauche
     */
    public function setPrenomAbonneUrssaf(string $prenomAbonneUrssaf): Embauche
    {
        $this->prenomAbonneUrssaf = $prenomAbonneUrssaf;
        return $this;
    }

    /**
     * @return string
     */
    public function getCodeUrssaf(): string
    {
        return $this->codeUrssaf ?? '';
    }

    /**
     * @param string $codeUrssaf
     * @return Embauche
     */
    public function setCodeUrssaf(string $codeUrssaf): Embauche
    {
        $this->codeUrssaf = $codeUrssaf;
        return $this;
    }

    /**
     * @return string
     */
    public function getAdresse1(): string
    {
        return $this->adresse1 ?? '';
    }

    /**
     * @param string $adresse1
     * @return Embauche
     */
    public function setAdresse1(string $adresse1): Embauche
    {
        $this->adresse1 = $adresse1;
        return $this;
    }

    /**
     * @return string
     */
    public function getAdresse2(): string
    {
        return $this->adresse2 ?? '';
    }

    /**
     * @param string $adresse2
     * @return Embauche
     */
    public function setAdresse2(string $adresse2): Embauche
    {
        $this->adresse2 = $adresse2;
        return $this;
    }

    /**
     * @return string
     */
    public function getCodePostal(): string
    {
        return $this->codePostal ?? '';
    }

    /**
     * @param string $codePostal
     * @return Embauche
     */
    public function setCodePostal(string $codePostal): Embauche
    {
        $this->codePostal = $codePostal;
        return $this;
    }

    /**
     * @return string
     */
    public function getVille(): string
    {
        return $this->ville ?? '';
    }

    /**
     * @param string $ville
     * @return Embauche
     */
    public function setVille(string $ville): Embauche
    {
        $this->ville = $ville;
        return $this;
    }

    /**
     * @return string
     */
    public function getTelephone(): string
    {
        return $this->telephone ?? '';
    }

    /**
     * @param string $telephone
     * @return Embauche
     */
    public function setTelephone(string $telephone): Embauche
    {
        $this->telephone = $telephone;
        return $this;
    }

    /**
     * @return string
     */
    public function getCodeNaf(): string
    {
        return $this->codeNaf ?? '';
    }

    /**
     * @param string $codeNaf
     * @return Embauche
     */
    public function setCodeNaf(string $codeNaf): Embauche
    {
        $this->codeNaf = $codeNaf;
        return $this;
    }

    /**
     * @return string
     */
    public function getCodeCentreMedecineTravail(): string
    {
        return $this->codeCentreMedecineTravail ?? '';
    }

    /**
     * @param string $codeCentreMedecineTravail
     * @return Embauche
     */
    public function setCodeCentreMedecineTravail(string $codeCentreMedecineTravail): Embauche
    {
        $this->codeCentreMedecineTravail = $codeCentreMedecineTravail;
        return $this;
    }

    /**
     * @return string
     */
    public function getSalarieNom(): string
    {
        return $this->salarieNom ?? '';
    }

    /**
     * @param string $salarieNom
     * @return Embauche
     */
    public function setSalarieNom(string $salarieNom): Embauche
    {
        $this->salarieNom = $salarieNom;
        return $this;
    }

    /**
     * @return string
     */
    public function getSalarieNomEpoux(): string
    {
        return $this->salarieNomEpoux ?? '';
    }

    /**
     * @param string $salarieNomEpoux
     * @return Embauche
     */
    public function setSalarieNomEpoux(string $salarieNomEpoux): Embauche
    {
        $this->salarieNomEpoux = $salarieNomEpoux;
        return $this;
    }

    /**
     * @return string
     */
    public function getSalariePrenom(): string
    {
        return $this->salariePrenom ?? '';
    }

    /**
     * @param string $salariePrenom
     * @return Embauche
     */
    public function setSalariePrenom(string $salariePrenom): Embauche
    {
        $this->salariePrenom = $salariePrenom;
        return $this;
    }

    /**
     * @return string
     */
    public function getSalarieNumeroSecu(): string
    {
        return $this->salarieNumeroSecu ?? '';
    }

    /**
     * @param string $salarieNumeroSecu
     * @return Embauche
     */
    public function setSalarieNumeroSecu(string $salarieNumeroSecu): Embauche
    {
        $this->salarieNumeroSecu = $salarieNumeroSecu;
        return $this;
    }

    /**
     * @return string
     */
    public function getSalarieSexe(): string
    {
        return $this->salarieSexe ?? '';
    }

    /**
     * @param string $salarieSexe
     * @return Embauche
     */
    public function setSalarieSexe(string $salarieSexe): Embauche
    {
        $this->salarieSexe = $salarieSexe;
        return $this;
    }

    /**
     * @return string
     */
    public function getSalarieDateNaissance(): string
    {
        return $this->salarieDateNaissance ?? '';
    }

    /**
     * @param string $salarieDateNaissance
     * @return Embauche
     */
    public function setSalarieDateNaissance(string $salarieDateNaissance): Embauche
    {
        $this->salarieDateNaissance = $salarieDateNaissance;
        return $this;
    }

    /**
     * @return string
     */
    public function getSalarieLieuNaissance(): string
    {
        return $this->salarieLieuNaissance ?? '';
    }

    /**
     * @param string $salarieLieuNaissance
     * @return Embauche
     */
    public function setSalarieLieuNaissance(string $salarieLieuNaissance): Embauche
    {
        $this->salarieLieuNaissance = $salarieLieuNaissance;
        return $this;
    }

    /**
     * @return string
     */
    public function getSalarieDepartementNaissance(): string
    {
        return $this->salarieDepartementNaissance ?? '';
    }

    /**
     * @param string $salarieDepartementNaissance
     * @return Embauche
     */
    public function setSalarieDepartementNaissance(string $salarieDepartementNaissance): Embauche
    {
        $this->salarieDepartementNaissance = $salarieDepartementNaissance;
        return $this;
    }

    /**
     * @return string
     */
    public function getDateEmbauche(): string
    {
        return $this->dateEmbauche ?? '';
    }

    /**
     * @param string $dateEmbauche
     * @return Embauche
     */
    public function setDateEmbauche(string $dateEmbauche): Embauche
    {
        $this->dateEmbauche = $dateEmbauche;
        return $this;
    }

    /**
     * @return string
     */
    public function getHeureEmbauche(): string
    {
        return $this->heureEmbauche ?? '';
    }

    /**
     * @param string $heureEmbauche
     * @return Embauche
     */
    public function setHeureEmbauche(string $heureEmbauche): Embauche
    {
        $this->heureEmbauche = $heureEmbauche;
        return $this;
    }

    /**
     * @return string
     */
    public function getTypeContrat(): string
    {
        return $this->typeContrat ?? '';
    }

    /**
     * @param string $typeContrat
     * @return Embauche
     */
    public function setTypeContrat(string $typeContrat): Embauche
    {
        $this->typeContrat = $typeContrat;
        return $this;
    }

    /**
     * @return string
     */
    public function getDateFinCDD(): string
    {
        return $this->dateFinCDD ?? '';
    }

    /**
     * @param string $dateFinCDD
     * @return Embauche
     */
    public function setDateFinCDD(string $dateFinCDD): Embauche
    {
        $this->dateFinCDD = $dateFinCDD;
        return $this;
    }

    /**
     * @return string
     */
    public function getDureePeriodeEssai(): string
    {
        return $this->dureePeriodeEssai ?? '';
    }

    /**
     * @param string $dureePeriodeEssai
     * @return Embauche
     */
    public function setDureePeriodeEssai(string $dureePeriodeEssai): Embauche
    {
        $this->dureePeriodeEssai = $dureePeriodeEssai;
        return $this;
    }

    /**
     * Retourne TRUE si l'embauche est correctement déclarée auprès de l'URSSAF,
     * et que le DPAE est finalisée, avec un n° de dossier.
     *
     * @return bool
     */
    public function isDeclaree(): bool
    {
        $codeRetourAr = $this->getDpae()->codeRetourAr ?? '';
        $refDossier = $this->getDpae()->refDossier ?? '';

        return ($codeRetourAr === '00' || $codeRetourAr === '98') && $refDossier !== '';
    }

    /**
     * Retourne TRUE si l'URSSAF a retourné un code erreur lors de la DPAE.
     * Le détail de l'erreur est visible dans `$this->>getDpae()->codeRetourArLibelle`.
     * Il faudra corriger l'erreur et renvoyer une nouvelle DPAE.
     *
     * @return bool
     */
    public function hasErreur(): bool
    {
        $codeRetourAr = $this->getDpae()->codeRetourAr ?? '';

        return $codeRetourAr && !$this->isDeclaree();
    }

    /**
     * @return ApiResource
     */
    public function getDpae(): ApiResource
    {
        return $this->dpae;
    }

    /**
     * @inheritDoc
     * @internal
     */
    public function jsonSerialize()
    {
        return $this->values;
    }
}