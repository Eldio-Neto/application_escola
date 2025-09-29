# Backlog de Pendências / Validação Estrutural
(Origem: inspeção superficial via listagem de diretórios e arquivos principais; conteúdo interno não avaliado.)

## 1. Estado Atual Detectado
- Estrutura Laravel presente em `backend/`.
- Integração planejada: `GETNET_INTEGRATION.md`.
- Ferramentas front-build: Tailwind, Vite, PostCSS.
- Diretório `frontend/` existe (conteúdo não listado nesta análise).
- Ausência visível de: Docker, CI, LICENSE, documentação arquitetural.

## 2. Itens de Domínio (Cursos & Operação)
- [ ] Modelos e migrations para: cursos, turmas/sessões, matrículas, frequência, instrutores, salas, pagamentos, cupons, certificados.
- [ ] Regras de capacidade e fila de espera (waitlist).
- [ ] Fluxo de matrícula (criação, cancelamento, transferência).
- [ ] Registro de presença (manual + possível QR).
- [ ] Geração de certificados (PDF + código de verificação).
- [ ] Relatórios: ocupação, receita, inadimplência, desempenho instrutor.
- [ ] Pesquisa/filtragem avançada de cursos.

## 3. Pagamentos & Financeiro
- [ ] Complementar integração Getnet (webhooks, reconciliação, idempotência).
- [ ] Suporte a cupons / vouchers.
- [ ] Regras de reembolso e prazos.
- [ ] Faturas / recibos (PDF).
- [ ] Parcelamento (se aplicável).
- [ ] Logs de transações e auditoria financeira.

## 4. Autenticação & Autorização
- [ ] Estratégia de papéis (admin, instrutor, aluno, financeiro).
- [ ] Policies / Gates para recursos sensíveis.
- [ ] Controle de acesso em API / rotas.
- [ ] Registro de ações (audit trail).

## 5. Frontend (SPA ou Híbrido)
- [ ] Estrutura de pastas (ex: src/, components/, views/, store/).
- [ ] Rotas (aluno, instrutor, admin).
- [ ] Estado global (Pinia/Vuex) para sessão, carrinho de matrícula, notificações.
- [ ] Telas: catálogo, detalhe do curso, fluxo de matrícula, pagamentos, dashboard aluno, dashboard instrutor.
- [ ] Feedback de erros e carregamento (skeletons, toasts).
- [ ] Internacionalização (pt-BR base).

## 6. Experiência do Aluno
- [ ] Dashboard com próximos cursos, status de pagamento, certificados.
- [ ] Histórico de matrículas.
- [ ] Acesso a materiais (controle de expiração / permissão).
- [ ] Avaliação pós-curso (surveys).

## 7. Instrutores
- [ ] Agenda / calendário.
- [ ] Lançamento de presença.
- [ ] Upload de materiais.
- [ ] Painel de desempenho (presenças, avaliações, taxa de conclusão).

## 8. Notificações & Comunicação
- [ ] Serviço central (fila) para e-mail/SMS/WhatsApp.
- [ ] Templates transacionais (confirmação, lembrete, pagamento pendente).
- [ ] Preferências de notificação.

## 9. Infra & Deploy
- [ ] Dockerfile + docker-compose para dev (PHP-FPM, Nginx, Node build, banco).
- [ ] Variáveis adicionais documentadas em `.env.example`.
- [ ] Ambiente para filas (Redis) e cache.
- [ ] Separação de builds frontend/back no pipeline.
- [ ] CDN/otimização de assets (quando houver).

## 10. CI/CD & Qualidade
- [ ] Pipeline (GitHub Actions) com: lint (PHP-CS-Fixer / Pint), PHPStan, PHPUnit, build frontend.
- [ ] Coverage mínimo definido.
- [ ] Testes de feature para fluxo de matrícula e pagamento.
- [ ] Testes de contratos (API) e de integração (webhooks).
- [ ] Análise estática de segurança (Laravel security advisories).

## 11. Observabilidade & Manutenção
- [ ] Logs estruturados (JSON) + correlação de request id.
- [ ] Monitoramento de jobs de fila.
- [ ] Métricas (ex: Prometheus/OpenTelemetry).
- [ ] Alertas (falha em pagamento, fila parada).
- [ ] Rotação / limpeza de logs.

## 12. Segurança & Compliance
- [ ] Rate limiting nas rotas públicas.
- [ ] Sanitização e validação de entrada robusta.
- [ ] Proteção a oversell de vagas (transações / locks).
- [ ] Política de privacidade e termos (aceite registrado).
- [ ] Conformidade LGPD (consentimento, exclusão/anonimização).
- [ ] Gestão de sessões e revogação.

## 13. Dados & Banco
- [ ] Diagramas (ERD) e documentação de tabelas.
- [ ] Seeds para ambiente de desenvolvimento.
- [ ] Estratégia de migrações evolutivas.
- [ ] Backups automatizados (não visível aqui).

## 14. Documentação
- [ ] Expansão de `README.md` (setup, propósito, stack).
- [ ] `ARCHITECTURE.md` (camadas, padrões, fluxo principal).
- [ ] `CONTRIBUTING.md` (padrões de contribuição).
- [ ] `API_SPEC.md` ou OpenAPI documentado.
- [ ] Guia de troubleshooting.
- [ ] CHANGELOG.md para versionamento semântico.

## 15. Licença & Metadados
- [ ] Adicionar LICENSE.
- [ ] Adicionar BADGES (build, coverage, versão).

## 16. Performance
- [ ] Testes de carga para picos de matrícula.
- [ ] Cache de listagem de cursos.
- [ ] Indexes no banco (métricas de uso para otimização futura).
- [ ] Estratégia de pagination server-side.

## 17. Extras Futuramente
- [ ] Waitlist com promoção automática.
- [ ] Check-in via QR code.
- [ ] Ponto de extensão para recomendações.
- [ ] Integração CRM / automação marketing.
- [ ] Exportação de dados pessoais (portabilidade).

## 18. Riscos Atuais (suposições)
- Falta de visibilidade do domínio real implementado.
- Ausência de testes pode dificultar evolução segura.
- Ausência de CI/CD torna integração e regressão mais prováveis.
- Sem documentação arquitetural aumenta curva de entrada.

## 19. Prioridade Inicial (Sugestão de Clusters)
1. Núcleo de domínio (cursos, turmas, matrículas, pagamentos base).
2. Autenticação, papéis e autorização.
3. Fluxo de pagamento estável + webhooks Getnet.
4. Dashboard aluno / instrutor mínimo.
5. Notificações transacionais.
6. Testes de fluxo crítico + CI.
7. Observabilidade e segurança.

## 20. Observação Final
Esta lista deriva somente dos diretórios e arquivos visíveis na listagem consultada; partes internas não foram inspecionadas, podendo alguns itens já existir.