import React, {Component} from 'react';

import Numeral from 'react-numeral';

class MfwIntType {
    static width = 60;
    static className = 'mfw-align-right';
    
    static render(text, record, index, column) {
        return <Numeral value={text} format={"0,0"}/>;
    }

    static sorter(a, b, column) {
        return a[column.dataIndex]-b[column.dataIndex];
    }
    
    static renderFilter(row, column) {
        return <Numeral value={row[column.dataIndex]} format={"0,0"}/>;
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
    
    static aggregatorCalc(type, result,) {
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
                return <Numeral value={value.sum} format={"0,0"}/>;
            case 'cnt':
                return <Numeral value={value.cnt} format={"0,0"}/>;
            case 'avg':
                return <Numeral value={value.cnt != 0 ? value.sum/value.cnt : 0} format={"0,0"}/>;
            default:
                return 'error type';
        }
    }    
};

export default MfwIntType;