# CHANGE LOG

## V2.2.1 (18/12/2025)

# PT-BR

- Atualizado a versao do composer. @GustavoSantarosa

# EN

- Updated the composer version. @GustavoSantarosa

## V2.2.0 (18/12/2025)

# PT-BR

- Corrigido um de para de code no basehandle para gate e policy. @GustavoSantarosa
- Atualizado a versao do composer. @GustavoSantarosa

# EN

- Fixed a code stop in the basehandle for gate and policy. @GustavoSantarosa
- Updated the composer version. @GustavoSantarosa

## V2.1.2 (11/11/2025)

# PT-BR

- Corrigido um erro na exportação via csv, quando havia relações dentro da linha a ser exportado, ele apresentava erro de array to string, removido todos os arrays internos a linha, para que nao ocorra mais esse erro. @GustavoSantarosa

# EN

- Fixed an error in the csv export when there were relationships within the row to be exported, it presented an array to string error, removed all internal arrays from the row to prevent this error from occurring. @GustavoSantarosa

## V2.1.0 (24/10/2025)

# PT-BR

- Adicionado a opção para exportar via csv. @GustavoSantarosa

# EN

- Added the option to export via csv. @GustavoSantarosa

## V2.0.13 (18/08/2025)

# PT-BR

- Realocado o setAllowedFilters e getAllowedFilters. @GustavoSantarosa

# EN

- Relocated the setAllowedFilters and getAllowedFilters. @GustavoSantarosa

## V2.0.12 (30/07/2025)

# PT-BR

- Comentado QueryException por estar quebrando no retorno. @GustavoSantarosa

# EN

- Commented out QueryException for breaking the return. @GustavoSantarosa

## V2.0.11 (27/06/2025)

# PT-BR

- Adicionando uma nova excessão para o base handler. @bhcosta90 in https://github.com/Quantum-Tecnology/Handler-Basics-Extension/pull/7

# EN

- Adding a new exception to the Handler base. @bhcosta90 in https://github.com/Quantum-Tecnology/Handler-Basics-Extension/pull/7

## V2.0.10 (24/04/2025)

# PT-BR

- Feito algumas melhorias para evitar dynamic properties. @GustavoSantarosa

# EN

- Made some improvements to avoid dynamic properties. @GustavoSantarosa

## V2.0.9 (17/04/2025)

# PT-BR

- Ajustado uma validação do allowedFilters. @GustavoSantarosa

# EN

- Adjusted a validation of the allowedFilters. @GustavoSantarosa

## V2.0.8 (10/04/2025)

# PT-BR

- Alterando a palavra include para includes, de acordo com o pacote do service. @bhcosta90
- Adicionando o max_per_page no retorno. @GustavoSantarosa

# EN

- Changing the word include to includes, according to the service package. @bhcosta90
- Added max_per_page to the response. @GustavoSantarosa

## V2.0.7 (06/04/2025)

# PT-BR

- Acrescentado o simple pagination. @GustavoSantarosa

# EN

- Added simple pagination. @GustavoSantarosa

## V2.0.6 (05/04/2025)

# PT-BR

- Esta ocorrendo erro que por default deve ser uma string vazia e não um boolean. @bhcosta90 in https://github.com/Quantum-Tecnology/Handler-Basics-Extension/pull/4

# EN

- This error is occurring which by default should be an empty string and not a boolean. @bhcosta90 in https://github.com/Quantum-Tecnology/Handler-Basics-Extension/pull/4

## V2.0.5 (01/04/2025)

# PT-BR

- Quando ocorrer erro de foreign key no banco de dados, a mensagem vai ser tratada. @bhcosta90

# EN

- When a foreign key error occurs in the database, the message will be handled. @bhcosta90

## V2.0.4 (15/03/2025)

# PT-BR

- Corrigindo a validação do include quando passado os campos para a relação. @GustavoSantarosa

# EN

- Fixed the validation of the include when the fields passed to the relationship. @GustavoSantarosa

## V2.0.3 (11/03/2025)

# PT-BR

- Fixado um erro latente relacionado ao allowedInclude na trait ApiResponseTrait @GustavoSantarosa

# EN

- Fixed a latent error related to allowedInclude in the ApiResponseTrait trait @GustavoSantarosa

## V2.0.2 (10/03/2025)

# PT-BR

- Fixado um erro ao sobreescrever o allowedInclude e allowedFilter @GustavoSantarosa
- Atualizado o composer com as novas atualizações usadas no pacote @GustavoSantarosa

# EN

- Fixed an error when overwriting allowedInclude and allowedFilter @GustavoSantarosa
- Updated the composer with the latest updates used in the package @GustavoSantarosa
