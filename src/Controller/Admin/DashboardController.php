<?php
namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Vinyl;
use App\Entity\Store;
use App\Entity\Reservation;
use App\Repository\UserRepository;
use App\Repository\VinylRepository;
use App\Repository\StoreRepository;
use App\Repository\ReservationRepository;
use App\Controller\Admin\UserCrudController;
use App\Controller\Admin\VinylCrudController;
use App\Controller\Admin\StoreCrudController;
use App\Controller\Admin\ReservationCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private UserRepository $userRepository,
        private VinylRepository $vinylRepository,
        private StoreRepository $storeRepository,
        private ReservationRepository $reservationRepository
    ) {
    }

    public function index(): Response
    {
        // Récupérer les statistiques
        $stats = [
            'totalUsers' => $this->userRepository->count([]),
            'totalVinyls' => $this->vinylRepository->count([]),
            'totalStores' => $this->storeRepository->count([]),
            'totalReservations' => $this->reservationRepository->count([]),
            'pendingReservations' => $this->reservationRepository->count(['status' => 'pending']),
            'confirmedReservations' => $this->reservationRepository->count(['status' => 'confirmed']),
            'recentUsers' => $this->userRepository->findBy([], ['id' => 'DESC'], 5),
            'recentReservations' => $this->reservationRepository->findBy([], ['createdAt' => 'DESC'], 5),
        ];

        // Afficher une page de dashboard personnalisée
        return $this->render('admin/dashboard.html.twig', [
            'stats' => $stats,
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('<b style="color: #3D0D0D;">🎵 Pick My Vinyl</b> <span style="color: #999; font-size: 0.8rem;">Admin</span>')
            ->setFaviconPath('images/pickmyvinyl.png') // Favicon personnalisé pour l'admin
            ->renderContentMaximized();
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('📊 Tableau de bord', 'fa fa-home');

        yield MenuItem::section('👥 Gestion des utilisateurs');
        yield MenuItem::linkToUrl('Tous les utilisateurs', 'fas fa-users', $this->generateCrudUrl(UserCrudController::class));

        yield MenuItem::section('🎵 Catalogue');
        yield MenuItem::linkToUrl('Vinyles', 'fas fa-compact-disc', $this->generateCrudUrl(VinylCrudController::class));
        yield MenuItem::linkToUrl('Magasins', 'fas fa-store-alt', $this->generateCrudUrl(StoreCrudController::class));

        yield MenuItem::section('📋 Réservations');
        yield MenuItem::linkToUrl('Toutes les réservations', 'fas fa-calendar-check', $this->generateCrudUrl(ReservationCrudController::class))
            ->setBadge($this->reservationRepository->count([]), 'info');
        yield MenuItem::linkToUrl('En attente', 'fas fa-hourglass-half', $this->generateCrudUrl(ReservationCrudController::class, ['status' => 'pending']))
            ->setBadge($this->reservationRepository->count(['status' => 'pending']), 'warning');

        yield MenuItem::section('🔧 Navigation');
        yield MenuItem::linkToRoute('🏠 Retour au site', 'fas fa-arrow-left', 'app_home');
        yield MenuItem::linkToLogout('🚪 Déconnexion', 'fa fa-sign-out');
    }

    private function generateCrudUrl(string $crudController, array $params = []): string
    {
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        $url = $adminUrlGenerator->setController($crudController);

        foreach ($params as $key => $value) {
            $url->set($key, $value);
        }

        return $url->generateUrl();
    }
}
