<?php

namespace App\Test\Controller;

use App\Entity\Subscription;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SubscriptionControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $repository;
    private string $path = '/subscription/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->repository = $this->manager->getRepository(Subscription::class);

        foreach ($this->repository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Subscription index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'subscription[endpoint]' => 'Testing',
            'subscription[p256dh]' => 'Testing',
            'subscription[auth]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->repository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Subscription();
        $fixture->setEndpoint('My Title');
        $fixture->setP256dh('My Title');
        $fixture->setAuth('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Subscription');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Subscription();
        $fixture->setEndpoint('Value');
        $fixture->setP256dh('Value');
        $fixture->setAuth('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'subscription[endpoint]' => 'Something New',
            'subscription[p256dh]' => 'Something New',
            'subscription[auth]' => 'Something New',
        ]);

        self::assertResponseRedirects('/subscription/');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getEndpoint());
        self::assertSame('Something New', $fixture[0]->getP256dh());
        self::assertSame('Something New', $fixture[0]->getAuth());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Subscription();
        $fixture->setEndpoint('Value');
        $fixture->setP256dh('Value');
        $fixture->setAuth('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/subscription/');
        self::assertSame(0, $this->repository->count([]));
    }
}
