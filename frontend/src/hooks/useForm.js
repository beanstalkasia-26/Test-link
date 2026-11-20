import { useCallback, useRef, useState } from 'react';
import { router } from '../router/navigation';

export default function useForm(initialValues = {}) {
    const defaults = useRef(initialValues);
    const transformer = useRef((data) => data);
    const [data, setDataState] = useState(initialValues);
    const [errors, setErrors] = useState({});
    const [processing, setProcessing] = useState(false);
    const [progress, setProgress] = useState(null);
    const [wasSuccessful, setWasSuccessful] = useState(false);
    const [recentlySuccessful, setRecentlySuccessful] = useState(false);

    const setData = useCallback((key, value) => {
        if (typeof key === 'string') setDataState((current) => ({ ...current, [key]: value }));
        else if (typeof key === 'function') setDataState((current) => key(current));
        else setDataState(key);
    }, []);

    const submit = (method, url, options = {}) => {
        setProcessing(true);
        setWasSuccessful(false);
        const payload = transformer.current(data);
        const call = method === 'delete' ? router.delete(url, { ...options, data: payload }) : router[method](url, payload, {
            ...options,
            onProgress: (event) => setProgress(event),
            onSuccess: (response) => {
                setErrors({});
                setWasSuccessful(true);
                setRecentlySuccessful(true);
                setTimeout(() => setRecentlySuccessful(false), 2000);
                options.onSuccess?.(response);
            },
            onError: (nextErrors) => {
                setErrors(nextErrors);
                options.onError?.(nextErrors);
            },
            onFinish: () => {
                setProcessing(false);
                setProgress(null);
                options.onFinish?.();
            },
        });
        return call;
    };

    return {
        data,
        setData,
        errors,
        hasErrors: Object.keys(errors).length > 0,
        processing,
        progress,
        wasSuccessful,
        recentlySuccessful,
        isDirty: JSON.stringify(data) !== JSON.stringify(defaults.current),
        transform: (fn) => {
            transformer.current = fn;
        },
        reset: (...fields) => {
            if (!fields.length) setDataState(defaults.current);
            else setDataState((current) => ({ ...current, ...Object.fromEntries(fields.map((f) => [f, defaults.current[f]])) }));
        },
        load: (values) => {
            defaults.current = values;
            setDataState(values);
        },
        setDefaults: (key, value) => {
            if (key === undefined) defaults.current = data;
            else if (typeof key === 'string') defaults.current = { ...defaults.current, [key]: value };
            else defaults.current = { ...defaults.current, ...key };
        },
        clearErrors: (...fields) => {
            if (!fields.length) setErrors({});
            else setErrors((current) => Object.fromEntries(Object.entries(current).filter(([k]) => !fields.includes(k))));
        },
        setError: (key, message) => {
            if (typeof key === 'string') setErrors((current) => ({ ...current, [key]: message }));
            else setErrors((current) => ({ ...current, ...key }));
        },
        submit,
        get: (url, options) => router.get(url, data, options),
        post: (url, options) => submit('post', url, options),
        put: (url, options) => submit('put', url, options),
        patch: (url, options) => submit('patch', url, options),
        delete: (url, options) => submit('delete', url, options),
    };
}
