# About Search CSDB Object

You can only do request csdb objects by passing parameters as below:

1. Write keywords just as common text.
1. Write keyword by prefixing the column name. eg.: "filename::DML-MALE"
1. The separator between prefix and keywords is only double colon (::)
1. The search result may perform one or more prefix.
1. If you perform more than one prefix, the algorithm is AND. It means that you look for firstPrefix:: and secondPrefix:: of every CSDB Object.
1. If no prefix, it means you look for the filename of CSDB Object

## Example
SearchText = ``` filename::MALE filename::0001Z date::2024-02-20 ```

The SearchText will look for CSDB Object which the filename contains `MALE` and `0001Z` and the its date contains `2024-02-20`.

## Available Column
1. filename | value: text
1. path | value: text
1. editable | value = 1 or 0
1. created_at | value = yyyy-mm-dd xx:xx:xx (24 hours format)
1. updated_at | value = yyyy-mm-dd xx:xx:xx (24 hours format)

### note
kalau path sudah masuk kedalam pencarian, maka akan mencari spesifik path
