import React, {Component} from 'react';

import MfwInteger from '@app/web/mfw/MfwInteger';

class MfwNativeIntType {
    static width = 100;
    static align = 'right';
    
    static render(text, record, index, column) {
        return text;
    }

    static sorter(a, b, column) {
        return a[column.dataIndex]-b[column.dataIndex];
    }
    
    static renderFilter(row, column) {
        return <MfwInteger value={row[column.dataIndex]}/>;
    }    
    
    static aggregatorInitValues(type) {
        switch(type) {
            case 'sum':
                return {sum: 0}
            case 'cnt':
                return {cnt: 0}
            case 'avg':
                return {
                    sum: 0,
                    cnt: 0
                }
            default:
                return null;
        }
    }
    
    static aggregatorCalc(type, result, row, dataIndex) {
        switch(type) {
            case 'sum':
                result.sum = result.sum + row[dataIndex]*1;
                break;
            case 'cnt':
                result.cnt++;
                break
            case 'avg':
                result.sum = result.sum + row[dataIndex]*1;
                result.cnt++;
                break;
        }
    }
    
    static aggregatorValue(type, result) {
        switch(type) {
            case 'sum':
                return result.sum;
            case 'cnt':
                return result.cnt;
            case 'avg':
                return result.cnt != 0 ? result.sum/result.cnt : 0
        }
    }    
    
    static renderTotal(type, value) {
        switch(type) {
            case 'sum':
                return <MfwInteger value={value.sum}/>;
            case 'cnt':
                return <MfwInteger value={value.cnt}/>;
            case 'avg':
                return <MfwInteger value={value.cnt != 0 ? value.sum/value.cnt : 0}/>;
            default:
                return 'error type';
        }
    }    
};

export default MfwNativeIntType;