# 📄 Sistema de Controle de Currículos

Um sistema web para gerenciamento das fases de contratação dos candidatos que tiveram seus currículos recebidos.

##  Funcionalidades

### Cadastro de Candidatos
- **Informações básicas**: Nome completo, data de nascimento, telefone, e-mail
- **Classificação**:
  - Área de interesse (Técnica/Administrativo)
  - Status (Pendente/Aprovado/Reprovado/Possível Contratação)
- **Observações**: Campo de descrição com formatação preservada

### 🔍 Filtros
| Filtro               | Opções                          |
|----------------------|---------------------------------|
| Área                 | Técnica / Administrativo        |
| Status               | 4 opções + "Todos"              |
| Busca                | Por nome ou e-mail              |
| Ordenação            | Por nome ou data de cadastro    |

### Gerenciamento
- Visualização detalhada
- Edição de descrição
- Atualização de status
- Exclusão lógica (marcação como inativo)

## Tecnologias

```mermaid
pie
    title Stack Tecnológico
    "PHP" : 45
    "MySQL" : 30
    "HTML/CSS" : 20
    "JavaScript" : 5
