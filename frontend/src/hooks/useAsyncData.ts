import { useQuery, QueryKey, UseQueryOptions } from '@tanstack/react-query';

export const useAsyncData = <TQueryFnData, TError = unknown>(
  key: QueryKey,
  fetcher: () => Promise<TQueryFnData>,
  options?: UseQueryOptions<TQueryFnData, TError, TQueryFnData>,
) => useQuery<TQueryFnData, TError>({ queryKey: key, queryFn: fetcher, ...options });
