import React, {Component} from 'react';

import { faCheck } from "@fortawesome/free-solid-svg-icons";
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';

class MfwCheckboxType {
    static width = 60;
    static align = 'center';
    
    static render(text, record, index, column) {
        return record[column.dataIndex] == column.checked ? <FontAwesomeIcon icon={faCheck}/> : null;
    }

    static sorter(a, b, column) {
        return a[column.dataIndex]-b[column.dataIndex];
    }
    
    static renderFilter(row, column) {
        return row[column.dataIndex];
    }
    
    static aggregatorInitValues(type) {
        switch(type) {
            case 'cnt':
                return {cnt: 0}
            default:
                return null;
        }
    }
    
    static aggregatorCalc(type, result, row, dataIndex) {
        switch(type) {
            case 'cnt':
                result.cnt++;
                break
        }
    }
    
    static aggregatorValue(type, result) {
        switch(type) {
            case 'cnt':
                return result.cnt;
            default:
                return 'error type';
        }
    }    
    
    static renderTotal(type, value) {
        switch(type) {
            case 'cnt':
                return <Numeral value={value.cnt} format={"0,0"}/>;
            default:
                return 'error type';
        }
    }    
};

export default MfwCheckboxType;