<?php

namespace App\Entity;

use App\Repository\ProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: ProjectRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['uuid'])]
#[UniqueEntity(fields: ['uuid'], message: 'There is already an project with this name')]
class Project
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $figmaLink = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $githubLink = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $state = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2, nullable: true)]
    private ?string $totalPrice = null;

    #[ORM\ManyToOne(inversedBy: 'projects')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'projects')]
    private ?Client $client = null;


    /**
     * @var Collection<int, Invoice>
     */
    #[ORM\OneToMany(targetEntity: Invoice::class, mappedBy: 'project', orphanRemoval: true)]
    private Collection $invoices;

    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2, nullable: true)]
    private ?string $estimatedPrice = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isPaying = null;

    #[ORM\Column(nullable: true)]
    private ?bool $database = null;

    #[ORM\Column(nullable: true)]
    private ?bool $maquette = null;

    #[ORM\Column(nullable: true)]
    private ?bool $maintenance = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $type = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $framework = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $options = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $device = null;


    /**
     * @var Collection<int, Pdf>
     */
    #[ORM\OneToMany(targetEntity: Pdf::class, mappedBy: 'project')]
    private Collection $pdfs;

    #[ORM\Column(nullable: true)]
    private ?int $maintenanceProject = null;

    #[ORM\Column(nullable: true)]
    private ?int $maintenancePercentage = null;

    #[ORM\Column(type: Types::TEXT, nullable: false)]
    private ?string $noteNames = null;

    #[ORM\Column(type: Types::TEXT, nullable: false)]
    private ?string $noteContent = null;

    #[ORM\OneToOne(mappedBy: 'project', cascade: ['persist', 'remove'])]
    private ?Chat $chat = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'autorisedInProjects')]
    private Collection $userAuthorised;

    #[ORM\Column(type: Types::TEXT, nullable: false)]
    private ?string $uuid = null;

    /**
     * @var Collection<int, Task>
     */
    #[ORM\OneToMany(targetEntity: Task::class, mappedBy: 'project')]
    private Collection $tasks;

    /**
     * @var Collection<int, Category>
     */
    #[ORM\OneToMany(targetEntity: Category::class, mappedBy: 'project')]
    private Collection $categories;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $websiteLink = null;

    #[ORM\Column(nullable: true)]
    private ?bool $otherUserCanEditInvoices = null;

    #[ORM\Column(nullable: true)]
    private ?bool $canOtherUserSeeClientProfile = null;

    #[ORM\Column]
    private ?bool $isCurrent = null;

    public function __construct()
    {
        $this->invoices = new ArrayCollection();
        $this->pdfs = new ArrayCollection();
        $this->userAuthorised = new ArrayCollection();
        $this->tasks = new ArrayCollection();
        $this->categories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getFigmaLink(): ?string
    {
        return $this->figmaLink;
    }

    public function setFigmaLink(?string $figmaLink): static
    {
        $this->figmaLink = $figmaLink;

        return $this;
    }

    public function getGithubLink(): ?string
    {
        return $this->githubLink;
    }

    public function setGithubLink(?string $githubLink): static
    {
        $this->githubLink = $githubLink;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(string $state): static
    {
        $this->state = $state;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeInterface $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getTotalPrice(): ?string
    {
        return $this->totalPrice;
    }

    public function setTotalPrice(?string $totalPrice): static
    {
        $this->totalPrice = $totalPrice;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): static
    {
        $this->client = $client;

        return $this;
    }



    /**
     * @return Collection<int, Invoice>
     */
    public function getInvoices(): Collection
    {
        return $this->invoices;
    }

    public function addInvoice(Invoice $invoice): static
    {
        if (!$this->invoices->contains($invoice)) {
            $this->invoices->add($invoice);
            $invoice->setProject($this);
        }

        return $this;
    }

    public function removeInvoice(Invoice $invoice): static
    {
        if ($this->invoices->removeElement($invoice)) {
            // set the owning side to null (unless already changed)
            if ($invoice->getProject() === $this) {
                $invoice->setProject(null);
            }
        }

        return $this;
    }

    public function getEstimatedPrice(): ?string
    {
        return $this->estimatedPrice;
    }

    public function setEstimatedPrice(?string $estimatedPrice): static
    {
        $this->estimatedPrice = $estimatedPrice;

        return $this;
    }

    public function isPaying(): ?bool
    {
        return $this->isPaying;
    }

    public function setPaying(?bool $isPaying): static
    {
        $this->isPaying = $isPaying;

        return $this;
    }

    public function isDatabase(): ?bool
    {
        return $this->database;
    }

    public function setDatabase(?bool $database): static
    {
        $this->database = $database;

        return $this;
    }

    public function isMaquette(): ?bool
    {
        return $this->maquette;
    }

    public function setMaquette(?bool $maquette): static
    {
        $this->maquette = $maquette;

        return $this;
    }

    public function isMaintenance(): ?bool
    {
        return $this->maintenance;
    }

    public function setMaintenance(?bool $maintenance): static
    {
        $this->maintenance = $maintenance;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getFramework(): ?string
    {
        return $this->framework;
    }

    public function setFramework(?string $framework): static
    {
        $this->framework = $framework;

        return $this;
    }

    public function getOptions(): ?string
    {
        return $this->options;
    }

    public function setOptions(?string $options): static
    {
        $this->options = $options;

        return $this;
    }

    public function getDevice(): ?string
    {
        return $this->device;
    }

    public function setDevice(?string $device): static
    {
        $this->device = $device;

        return $this;
    }


    /**
     * @return Collection<int, Pdf>
     */
    public function getPdfs(): Collection
    {
        return $this->pdfs;
    }

    public function addPdf(Pdf $pdf): static
    {
        if (!$this->pdfs->contains($pdf)) {
            $this->pdfs->add($pdf);
            $pdf->setProject($this);
        }

        return $this;
    }

    public function removePdf(Pdf $pdf): static
    {
        if ($this->pdfs->removeElement($pdf)) {
            // set the owning side to null (unless already changed)
            if ($pdf->getProject() === $this) {
                $pdf->setProject(null);
            }
        }

        return $this;
    }

    public function getMaintenanceProject(): ?int
    {
        return $this->maintenanceProject;
    }

    public function setMaintenanceProject(?int $maintenanceProject): static
    {
        $this->maintenanceProject = $maintenanceProject;

        return $this;
    }

    public function getMaintenancePercentage(): ?int
    {
        return $this->maintenancePercentage;
    }

    public function setMaintenancePercentage(?int $maintenancePercentage): static
    {
        $this->maintenancePercentage = $maintenancePercentage;

        return $this;
    }

    public function getNoteNames(): ?string
    {
        return $this->noteNames;
    }

    public function setNoteNames(?string $noteNames): static
    {
        $this->noteNames = $noteNames;

        return $this;
    }

    public function getNoteContent(): ?string
    {
        return $this->noteContent;
    }

    public function setNoteContent(?string $noteContent): static
    {
        $this->noteContent = $noteContent;

        return $this;
    }

    public function getChat(): ?Chat
    {
        return $this->chat;
    }

    public function setChat(Chat $chat): static
    {
        // set the owning side of the relation if necessary
        if ($chat->getProject() !== $this) {
            $chat->setProject($this);
        }

        $this->chat = $chat;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUserAuthorised(): Collection
    {
        return $this->userAuthorised;
    }

    public function addUserAuthorised(User $userAuthorised): static
    {
        if (!$this->userAuthorised->contains($userAuthorised)) {
            $this->userAuthorised->add($userAuthorised);
        }

        return $this;
    }
    public function hasUserInUserAuthorised(User $userAuthorised): bool
    {
        if ($this->userAuthorised->contains($userAuthorised)) {
            return true;
        }
        return false;
    }

    public function removeUserAuthorised(User $userAuthorised): static
    {
        $this->userAuthorised->removeElement($userAuthorised);

        return $this;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(?string $uuid): static
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * @return Collection<int, Task>
     */
    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    public function addTask(Task $task): static
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks->add($task);
            $task->setProject($this);
        }

        return $this;
    }

    public function removeTask(Task $task): static
    {
        if ($this->tasks->removeElement($task)) {
            // set the owning side to null (unless already changed)
            if ($task->getProject() === $this) {
                $task->setProject(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
            $category->setProject($this);
        }

        return $this;
    }

    public function removeCategory(Category $category): static
    {
        if ($this->categories->removeElement($category)) {
            // set the owning side to null (unless already changed)
            if ($category->getProject() === $this) {
                $category->setProject(null);
            }
        }

        return $this;
    }

    public function getWebsiteLink(): ?string
    {
        return $this->websiteLink;
    }

    public function setWebsiteLink(?string $websiteLink): static
    {
        $this->websiteLink = $websiteLink;

        return $this;
    }

    public function isOtherUserCanEditInvoices(): ?bool
    {
        return $this->otherUserCanEditInvoices;
    }

    public function setOtherUserCanEditInvoices(?bool $otherUserCanEditInvoices): static
    {
        $this->otherUserCanEditInvoices = $otherUserCanEditInvoices;

        return $this;
    }

    public function isCanOtherUserSeeClientProfile(): ?bool
    {
        return $this->canOtherUserSeeClientProfile;
    }

    public function setCanOtherUserSeeClientProfile(?bool $canOtherUserSeeClientProfile): static
    {
        $this->canOtherUserSeeClientProfile = $canOtherUserSeeClientProfile;

        return $this;
    }

    public function isCurrent(): ?bool
    {
        return $this->isCurrent;
    }

    public function setCurrent(bool $isCurrent): static
    {
        $this->isCurrent = $isCurrent;

        return $this;
    }

}
