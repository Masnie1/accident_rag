---
id: INC-16
titre: Une file RabbitMQ s'accumule
service: messagerie
gravite: moyenne
alertes:
  - "Queue length above threshold"
  - "RabbitMQ queue has 0 consumers"
  - "Messages ready above 10000"
revision: 2026-09-18
---

## Symptômes
Le nombre de messages en attente dans une file augmente sans redescendre. Les traitements qui dépendent de cette file ne produisent plus de résultat.

## Impact
Les traitements asynchrones prennent du retard. Si la mémoire ou le disque du broker saturent, les applications ne peuvent plus publier de messages.

## Diagnostic
1. État des files : `docker exec <rabbitmq> rabbitmqctl list_queues name messages_ready messages_unacknowledged consumers`
2. `consumers` à 0 : le consommateur est arrêté. Vérifier son conteneur : `docker ps -a --filter "name=<consommateur>"` et `docker logs --tail 100 <consommateur>`
3. `consumers` à 1 ou plus mais `messages_unacknowledged` élevé : le consommateur est bloqué sur un message ou trop lent.
4. Alarmes mémoire ou disque du broker : `docker exec <rabbitmq> rabbitmq-diagnostics alarms`

## Actions
1. Consommateur arrêté : `docker compose up -d <consommateur>`, puis vérifier que la file redescend.
2. Consommateur bloqué : le redémarrer (`docker restart <consommateur>`). Les messages non acquittés sont remis dans la file. Si le même message bloque de nouveau, c'est un message empoisonné : escalader.
3. Alarme disque : appliquer INC-01.
4. Ne jamais vider la file (`purge_queue`) sans validation : les messages seraient perdus.

## Escalade
Escalader au N2 applicatif.

## Vérification
`messages_ready` diminue régulièrement et `consumers` vaut au moins 1.
