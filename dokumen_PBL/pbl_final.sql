--
-- PostgreSQL database cluster dump
--

-- Started on 2025-12-30 22:44:21

\restrict nKhANMFuwj9nGovn8OahwxhFGdlhPc8u9wF68FCyXU1vvg1hele2VOfvDTuAOQF

SET default_transaction_read_only = off;

SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;

--
-- Roles
--

CREATE ROLE postgres;
ALTER ROLE postgres WITH SUPERUSER INHERIT CREATEROLE CREATEDB LOGIN REPLICATION BYPASSRLS;

--
-- User Configurations
--








\unrestrict nKhANMFuwj9nGovn8OahwxhFGdlhPc8u9wF68FCyXU1vvg1hele2VOfvDTuAOQF

--
-- Databases
--

--
-- Database "template1" dump
--

\connect template1

--
-- PostgreSQL database dump
--

\restrict EPHNWZqTaOnQYR12Hm9VPPpn31WZbsdGsFme3xBISBzm9H3owGVSCV2jxyzFqJ9

-- Dumped from database version 15.14
-- Dumped by pg_dump version 15.14

-- Started on 2025-12-30 22:44:21

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

-- Completed on 2025-12-30 22:44:21

--
-- PostgreSQL database dump complete
--

\unrestrict EPHNWZqTaOnQYR12Hm9VPPpn31WZbsdGsFme3xBISBzm9H3owGVSCV2jxyzFqJ9

--
-- Database "pbl" dump
--

--
-- PostgreSQL database dump
--

\restrict vH3V8lOY4Y5zPPUgXqj8OGHgQdoOoNy9SadSTtyueHocgGq2BHCtNHMyUFqZ9PY

-- Dumped from database version 15.14
-- Dumped by pg_dump version 15.14

-- Started on 2025-12-30 22:44:21

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- TOC entry 3506 (class 1262 OID 18039)
-- Name: pbl; Type: DATABASE; Schema: -; Owner: postgres
--

CREATE DATABASE pbl WITH TEMPLATE = template0 ENCODING = 'UTF8' LOCALE_PROVIDER = libc LOCALE = 'English_United States.1252';


ALTER DATABASE pbl OWNER TO postgres;

\unrestrict vH3V8lOY4Y5zPPUgXqj8OGHgQdoOoNy9SadSTtyueHocgGq2BHCtNHMyUFqZ9PY
\connect pbl
\restrict vH3V8lOY4Y5zPPUgXqj8OGHgQdoOoNy9SadSTtyueHocgGq2BHCtNHMyUFqZ9PY

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- TOC entry 864 (class 1247 OID 18048)
-- Name: booking_status; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE public.booking_status AS ENUM (
    'diajukan',
    'disetujui',
    'ditolak',
    'selesai'
);


ALTER TYPE public.booking_status OWNER TO postgres;

--
-- TOC entry 861 (class 1247 OID 18041)
-- Name: user_role; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE public.user_role AS ENUM (
    'admin',
    'dosen',
    'mahasiswa'
);


ALTER TYPE public.user_role OWNER TO postgres;

--
-- TOC entry 238 (class 1255 OID 18072)
-- Name: set_updated_at(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.set_updated_at() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
  NEW.updated_at = now();
  RETURN NEW;
END;
$$;


ALTER FUNCTION public.set_updated_at() OWNER TO postgres;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- TOC entry 231 (class 1259 OID 18448)
-- Name: blocked_dates; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.blocked_dates (
    block_id bigint NOT NULL,
    sarana_id bigint NOT NULL,
    start_date date NOT NULL,
    end_date date NOT NULL,
    start_time time without time zone NOT NULL,
    end_time time without time zone NOT NULL,
    reason text,
    created_by bigint,
    created_at timestamp without time zone DEFAULT now()
);


ALTER TABLE public.blocked_dates OWNER TO postgres;

--
-- TOC entry 230 (class 1259 OID 18447)
-- Name: blocked_dates_block_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.blocked_dates_block_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.blocked_dates_block_id_seq OWNER TO postgres;

--
-- TOC entry 3507 (class 0 OID 0)
-- Dependencies: 230
-- Name: blocked_dates_block_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.blocked_dates_block_id_seq OWNED BY public.blocked_dates.block_id;


--
-- TOC entry 229 (class 1259 OID 18299)
-- Name: bookings; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.bookings (
    booking_id bigint NOT NULL,
    mahasiswa_nim character varying(20),
    booking_dosen_nidn character varying(20) NOT NULL,
    sarana_id bigint NOT NULL,
    tanggal date NOT NULL,
    sks integer NOT NULL,
    hours_per_sks numeric(6,2) NOT NULL,
    duration_hours numeric(8,2) NOT NULL,
    start_time time without time zone NOT NULL,
    end_time time without time zone NOT NULL,
    keperluan text,
    status public.booking_status DEFAULT 'diajukan'::public.booking_status NOT NULL,
    created_by bigint,
    created_at timestamp without time zone DEFAULT now(),
    handled_by bigint,
    admin_response text,
    CONSTRAINT bookings_check CHECK ((end_time > start_time)),
    CONSTRAINT bookings_duration_hours_check CHECK ((duration_hours >= (0)::numeric)),
    CONSTRAINT bookings_hours_per_sks_check CHECK ((hours_per_sks >= (0)::numeric)),
    CONSTRAINT bookings_sks_check CHECK ((sks > 0))
);


ALTER TABLE public.bookings OWNER TO postgres;

--
-- TOC entry 228 (class 1259 OID 18298)
-- Name: bookings_booking_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.bookings_booking_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.bookings_booking_id_seq OWNER TO postgres;

--
-- TOC entry 3508 (class 0 OID 0)
-- Dependencies: 228
-- Name: bookings_booking_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.bookings_booking_id_seq OWNED BY public.bookings.booking_id;


--
-- TOC entry 223 (class 1259 OID 18149)
-- Name: content_categories; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.content_categories (
    category_id bigint NOT NULL,
    nama character varying(150) NOT NULL,
    description text
);


ALTER TABLE public.content_categories OWNER TO postgres;

--
-- TOC entry 222 (class 1259 OID 18148)
-- Name: content_categories_category_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.content_categories_category_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.content_categories_category_id_seq OWNER TO postgres;

--
-- TOC entry 3509 (class 0 OID 0)
-- Dependencies: 222
-- Name: content_categories_category_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.content_categories_category_id_seq OWNED BY public.content_categories.category_id;


--
-- TOC entry 225 (class 1259 OID 18160)
-- Name: contents; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.contents (
    content_id bigint NOT NULL,
    slug character varying(255) NOT NULL,
    title character varying(400) NOT NULL,
    excerpt text,
    body text,
    category_id bigint,
    featured_image text,
    admin_id bigint,
    is_published boolean DEFAULT false,
    published_at timestamp without time zone,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now()
);


ALTER TABLE public.contents OWNER TO postgres;

--
-- TOC entry 224 (class 1259 OID 18159)
-- Name: contents_content_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.contents_content_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.contents_content_id_seq OWNER TO postgres;

--
-- TOC entry 3510 (class 0 OID 0)
-- Dependencies: 224
-- Name: contents_content_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.contents_content_id_seq OWNED BY public.contents.content_id;


--
-- TOC entry 216 (class 1259 OID 18074)
-- Name: dosen; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.dosen (
    nidn character varying(20) NOT NULL,
    user_id bigint,
    nama character varying(150) NOT NULL,
    jabatan character varying(100),
    email character varying(150),
    foto_path text,
    researchgate_url text,
    scholar_url text,
    sinta_url text,
    created_at timestamp without time zone DEFAULT now(),
    nip character varying(30),
    prodi character varying(100),
    pendidikan text,
    sertifikasi text,
    mata_kuliah text
);


ALTER TABLE public.dosen OWNER TO postgres;

--
-- TOC entry 237 (class 1259 OID 18769)
-- Name: dosen_focus; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.dosen_focus (
    nidn character varying(20) NOT NULL,
    focus_id bigint NOT NULL
);


ALTER TABLE public.dosen_focus OWNER TO postgres;

--
-- TOC entry 219 (class 1259 OID 18122)
-- Name: galeri; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.galeri (
    galeri_id bigint NOT NULL,
    judul character varying(255) NOT NULL,
    deskripsi text,
    file_path text,
    tanggal date DEFAULT CURRENT_DATE NOT NULL,
    uploaded_by bigint,
    created_at timestamp without time zone DEFAULT now()
);


ALTER TABLE public.galeri OWNER TO postgres;

--
-- TOC entry 218 (class 1259 OID 18121)
-- Name: galeri_galeri_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.galeri_galeri_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.galeri_galeri_id_seq OWNER TO postgres;

--
-- TOC entry 3511 (class 0 OID 0)
-- Dependencies: 218
-- Name: galeri_galeri_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.galeri_galeri_id_seq OWNED BY public.galeri.galeri_id;


--
-- TOC entry 234 (class 1259 OID 18673)
-- Name: kategori_publikasi; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.kategori_publikasi (
    id integer NOT NULL,
    nama_kategori character varying(50) NOT NULL
);


ALTER TABLE public.kategori_publikasi OWNER TO postgres;

--
-- TOC entry 233 (class 1259 OID 18672)
-- Name: kategori_publikasi_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.kategori_publikasi_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.kategori_publikasi_id_seq OWNER TO postgres;

--
-- TOC entry 3512 (class 0 OID 0)
-- Dependencies: 233
-- Name: kategori_publikasi_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.kategori_publikasi_id_seq OWNED BY public.kategori_publikasi.id;


--
-- TOC entry 217 (class 1259 OID 18089)
-- Name: mahasiswa; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.mahasiswa (
    nim character varying(20) NOT NULL,
    user_id bigint,
    nama character varying(150) NOT NULL,
    prodi character varying(100),
    tingkat character varying(50),
    no_hp character varying(25),
    email character varying(150),
    created_at timestamp without time zone DEFAULT now(),
    initial_password character varying(20)
);


ALTER TABLE public.mahasiswa OWNER TO postgres;

--
-- TOC entry 236 (class 1259 OID 18680)
-- Name: publikasi; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.publikasi (
    id integer NOT NULL,
    judul character varying(255) NOT NULL,
    external_link text NOT NULL,
    kategori_id integer,
    dosen_nidn character varying(20) NOT NULL,
    status character varying(20) DEFAULT 'pending'::character varying,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    focus_id integer,
    tahun integer
);


ALTER TABLE public.publikasi OWNER TO postgres;

--
-- TOC entry 235 (class 1259 OID 18679)
-- Name: publikasi_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.publikasi_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.publikasi_id_seq OWNER TO postgres;

--
-- TOC entry 3513 (class 0 OID 0)
-- Dependencies: 235
-- Name: publikasi_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.publikasi_id_seq OWNED BY public.publikasi.id;


--
-- TOC entry 227 (class 1259 OID 18248)
-- Name: research_focus; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.research_focus (
    focus_id bigint NOT NULL,
    nama_fokus character varying(200) NOT NULL,
    description text
);


ALTER TABLE public.research_focus OWNER TO postgres;

--
-- TOC entry 226 (class 1259 OID 18247)
-- Name: research_focus_focus_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.research_focus_focus_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.research_focus_focus_id_seq OWNER TO postgres;

--
-- TOC entry 3514 (class 0 OID 0)
-- Dependencies: 226
-- Name: research_focus_focus_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.research_focus_focus_id_seq OWNED BY public.research_focus.focus_id;


--
-- TOC entry 221 (class 1259 OID 18138)
-- Name: sarana; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.sarana (
    sarana_id bigint NOT NULL,
    nama_sarana character varying(200) NOT NULL,
    deskripsi text,
    tipe character varying(100),
    status character varying(50) DEFAULT 'aktif'::character varying,
    foto_path text,
    created_at timestamp without time zone DEFAULT now()
);


ALTER TABLE public.sarana OWNER TO postgres;

--
-- TOC entry 220 (class 1259 OID 18137)
-- Name: sarana_sarana_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.sarana_sarana_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.sarana_sarana_id_seq OWNER TO postgres;

--
-- TOC entry 3515 (class 0 OID 0)
-- Dependencies: 220
-- Name: sarana_sarana_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.sarana_sarana_id_seq OWNED BY public.sarana.sarana_id;


--
-- TOC entry 215 (class 1259 OID 18058)
-- Name: users; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.users (
    user_id bigint NOT NULL,
    username character varying(100) NOT NULL,
    password_hash character varying(255) NOT NULL,
    role public.user_role NOT NULL,
    email character varying(150),
    display_name character varying(150),
    created_at timestamp without time zone DEFAULT now()
);


ALTER TABLE public.users OWNER TO postgres;

--
-- TOC entry 214 (class 1259 OID 18057)
-- Name: users_user_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.users_user_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.users_user_id_seq OWNER TO postgres;

--
-- TOC entry 3516 (class 0 OID 0)
-- Dependencies: 214
-- Name: users_user_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.users_user_id_seq OWNED BY public.users.user_id;


--
-- TOC entry 232 (class 1259 OID 18621)
-- Name: vw_peminjaman_history; Type: MATERIALIZED VIEW; Schema: public; Owner: postgres
--

CREATE MATERIALIZED VIEW public.vw_peminjaman_history AS
 SELECT b.created_at,
    u_display.display_name AS requester_display_name,
    b.booking_id,
    b.tanggal,
    s.nama_sarana,
    m.nim AS mahasiswa_nim,
    m.nama AS mahasiswa_nama,
    d.nidn AS dosen_nidn,
    d.nama AS dosen_nama,
    b.keperluan,
    b.sks,
    b.start_time,
    b.end_time,
    b.handled_by,
    b.admin_response,
    b.status
   FROM ((((public.bookings b
     JOIN public.sarana s ON ((b.sarana_id = s.sarana_id)))
     JOIN public.mahasiswa m ON (((b.mahasiswa_nim)::text = (m.nim)::text)))
     JOIN public.dosen d ON (((b.booking_dosen_nidn)::text = (d.nidn)::text)))
     JOIN public.users u_display ON ((b.created_by = u_display.user_id)))
  ORDER BY b.tanggal DESC
  WITH NO DATA;


ALTER TABLE public.vw_peminjaman_history OWNER TO postgres;

--
-- TOC entry 3260 (class 2604 OID 18451)
-- Name: blocked_dates block_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.blocked_dates ALTER COLUMN block_id SET DEFAULT nextval('public.blocked_dates_block_id_seq'::regclass);


--
-- TOC entry 3257 (class 2604 OID 18302)
-- Name: bookings booking_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bookings ALTER COLUMN booking_id SET DEFAULT nextval('public.bookings_booking_id_seq'::regclass);


--
-- TOC entry 3251 (class 2604 OID 18152)
-- Name: content_categories category_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.content_categories ALTER COLUMN category_id SET DEFAULT nextval('public.content_categories_category_id_seq'::regclass);


--
-- TOC entry 3252 (class 2604 OID 18163)
-- Name: contents content_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.contents ALTER COLUMN content_id SET DEFAULT nextval('public.contents_content_id_seq'::regclass);


--
-- TOC entry 3245 (class 2604 OID 18125)
-- Name: galeri galeri_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.galeri ALTER COLUMN galeri_id SET DEFAULT nextval('public.galeri_galeri_id_seq'::regclass);


--
-- TOC entry 3262 (class 2604 OID 18676)
-- Name: kategori_publikasi id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.kategori_publikasi ALTER COLUMN id SET DEFAULT nextval('public.kategori_publikasi_id_seq'::regclass);


--
-- TOC entry 3263 (class 2604 OID 18683)
-- Name: publikasi id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.publikasi ALTER COLUMN id SET DEFAULT nextval('public.publikasi_id_seq'::regclass);


--
-- TOC entry 3256 (class 2604 OID 18251)
-- Name: research_focus focus_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.research_focus ALTER COLUMN focus_id SET DEFAULT nextval('public.research_focus_focus_id_seq'::regclass);


--
-- TOC entry 3248 (class 2604 OID 18141)
-- Name: sarana sarana_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sarana ALTER COLUMN sarana_id SET DEFAULT nextval('public.sarana_sarana_id_seq'::regclass);


--
-- TOC entry 3241 (class 2604 OID 18061)
-- Name: users user_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users ALTER COLUMN user_id SET DEFAULT nextval('public.users_user_id_seq'::regclass);


--
-- TOC entry 3494 (class 0 OID 18448)
-- Dependencies: 231
-- Data for Name: blocked_dates; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.blocked_dates (block_id, sarana_id, start_date, end_date, start_time, end_time, reason, created_by, created_at) FROM stdin;
\.


--
-- TOC entry 3492 (class 0 OID 18299)
-- Dependencies: 229
-- Data for Name: bookings; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.bookings (booking_id, mahasiswa_nim, booking_dosen_nidn, sarana_id, tanggal, sks, hours_per_sks, duration_hours, start_time, end_time, keperluan, status, created_by, created_at, handled_by, admin_response) FROM stdin;
30	250311619109	0021058301	1	2025-12-28	2	2.00	3.33	07:00:00	10:20:00	kaabi back to polinema	disetujui	41	2025-12-16 21:11:03.228875	14	\N
9	244107060101	0308018702	1	2025-12-13	2	2.00	3.33	10:30:00	13:50:00	asadsh	ditolak	14	2025-12-11 19:08:54.86298	14	harap berikan keterangan keperluan yang jelas
11	244107060101	0017129402	1	2025-12-17	2	2.00	3.33	07:50:00	11:10:00	teeessssssssssss	ditolak	14	2025-12-12 01:27:07.889348	14	ga jelas
12	244107060101	0007108905	1	2025-12-22	3	2.00	5.00	08:40:00	13:40:00	testing 2	disetujui	14	2025-12-12 01:27:17.110418	14	\N
13	244107060101	0005078102	1	2025-12-25	4	2.00	6.67	09:40:00	16:20:00	testing 3	disetujui	14	2025-12-12 01:27:21.823062	14	\N
14	244107020163	0007108905	1	2025-12-18	2	2.00	3.33	07:50:00	11:10:00	teesssss	disetujui	23	2025-12-13 02:32:48.711422	14	\N
16	244107020163	0009118305	1	2025-12-16	2	2.00	3.33	08:00:00	11:20:00	muak aku	diajukan	23	2025-12-13 02:40:19.413758	\N	\N
17	244107020163	0005078102	1	2025-12-23	2	2.00	3.33	11:00:00	14:20:00	asadsda	diajukan	14	2025-12-13 03:05:41.845958	\N	\N
18	244107020163	0021058301	1	2025-12-24	2	2.00	3.33	09:30:00	12:50:00	asdggh	disetujui	23	2025-12-13 03:21:19.230866	14	\N
19	244107020163	0031019404	1	2025-12-27	5	2.00	8.33	09:45:00	18:05:00	qwer	ditolak	23	2025-12-13 05:01:11.476478	14	kasi alasan yang jelas
20	112233445566	0010117109	1	2025-12-27	2	2.00	3.33	10:00:00	13:20:00	tes buat view	disetujui	14	2025-12-15 15:40:56.748787	14	\N
24	25410706123	0021058301	1	2025-12-30	4	2.00	6.67	08:50:00	15:30:00	ini adekku	disetujui	28	2025-12-15 18:13:39.417139	14	\N
25	25410706123	0021058301	1	2025-12-26	2	2.00	3.33	09:20:00	12:40:00	tes tess tesss	ditolak	14	2025-12-15 18:24:40.633408	14	buat ngetess ajaaa
26	25410706123	0031019404	1	2025-12-26	2	2.00	3.33	11:00:00	14:20:00	testing jadwalll	disetujui	28	2025-12-15 20:56:59.100773	14	\N
31	123456789	0308018702	1	2025-12-31	2	2.00	3.33	07:00:00	10:20:00	qwwewgr	disetujui	42	2025-12-18 20:26:29.337043	14	\N
28	25410706123	1234567890	1	2025-12-31	2	2.00	3.33	12:50:00	16:10:00	hallooooo222	disetujui	14	2025-12-16 20:43:37.763035	14	\N
21	244107020163	0019038905	1	2025-12-28	6	2.00	10.00	09:45:00	19:45:00	tes view lagiii	ditolak	23	2025-12-16 20:43:58.207191	14	kasi bunga dulu
5	244107060101	0021058301	1	2025-12-12	2	2.00	3.33	07:00:00	10:20:00	apb	disetujui	14	2025-12-26 22:15:27.386047	14	\N
15	244107020163	0009118305	1	2025-12-19	2	2.00	3.33	08:00:00	11:20:00	lagi lagi tes	ditolak	14	2025-12-28 22:07:02.040979	14	mohon berikan alasan yang jelas
\.


--
-- TOC entry 3486 (class 0 OID 18149)
-- Dependencies: 223
-- Data for Name: content_categories; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.content_categories (category_id, nama, description) FROM stdin;
1	Berita	Kategori untuk berita utama, liputan kegiatan, dan artikel terkini jurusan.
2	Pengumuman	Informasi penting terkait akademik, beasiswa, dan kebijakan kampus.
3	Agenda	Jadwal kegiatan, seminar, sidang skripsi, dan acara mendatang.
4	Prestasi	Dokumentasi pencapaian dan kejuaraan yang diraih oleh mahasiswa maupun dosen.
\.


--
-- TOC entry 3488 (class 0 OID 18160)
-- Dependencies: 225
-- Data for Name: contents; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.contents (content_id, slug, title, excerpt, body, category_id, featured_image, admin_id, is_published, published_at, created_at, updated_at) FROM stdin;
1	mahasiswa-d4-sib-juara-1-rk-ink-blend	Mahasiswa D4 Sistem Informasi Bisnis Jurusan Teknologi Informasi meraih Juara 1 ajang RK INK BLEND COMPETITION!	Malang, 28 November 2025 – Pada ajang RK INK BLEND COMPETITION yang diselenggarakan oleh RumahKIPK Universitas Siliwangi, mahasiswa inspiratif kami...	<p>Selamat kepada tim mahasiswa D4 SIB yang telah berhasil meraih Juara 1. Prestasi ini menjadi bukti kompetensi mahasiswa Polinema di kancah nasional.</p>	4	https://jti.polinema.ac.id/wp-content/uploads/2025/11/WhatsApp-Image-2025-11-28-at-13.31.28-585x600.jpeg	1	t	2025-11-28 00:00:00	2025-12-03 14:55:13.704364	2025-12-03 14:55:13.704364
2	kabar-membanggakan-prodi-sib-pimnas	Kabar membanggakan kembali hadir dari mahasiswa Program Studi Sistem Informasi Bisnis	Malang, 28 November 2025 – Jurusan Teknologi Informasi Politeknik Negeri Malang kembali mengukir prestasi gemilang. Kali ini dari Prodi D4...	<p>Mahasiswa SIB kembali menorehkan tinta emas dalam ajang Pekan Ilmiah Mahasiswa Nasional (PIMNAS). Semangat inovasi terus dikembangkan di jurusan TI.</p>	4	https://jti.polinema.ac.id/wp-content/uploads/2025/11/WhatsApp-Image-2025-11-28-at-12.55.06-1170x600.jpeg	1	t	2025-11-28 00:00:00	2025-12-03 14:55:13.704364	2025-12-03 14:55:13.704364
3	prestasi-lomba-cipta-nusantara-fest	Prestasi kembali diraih oleh mahasiswa Program Studi Sistem Informasi Bisnis dalam ajang Lomba Cipta Nusantara Fest	Malang, 27 November 2025 – Prestasi membanggakan kembali ditorehkan oleh mahasiswa Program Studi Sistem Informasi Bisnis dalam ajang Lomba Cipta...	<p>Tim mahasiswa berhasil membawa pulang medali dalam ajang Lomba Cipta Nusantara Fest. Kompetisi ini diikuti oleh berbagai perguruan tinggi di Indonesia.</p>	4	https://jti.polinema.ac.id/wp-content/uploads/2025/11/WhatsApp-Image-2025-11-28-at-12.55.06-1170x600.jpeg	1	t	2025-11-27 00:00:00	2025-12-03 14:55:13.704364	2025-12-03 14:55:13.704364
4	prestasi-gemilang-entrepreneurs-festival-2025	Prestasi Gemilang! Mahasiswa Prodi D4 Sistem Informasi Bisnis Juara di Entrepreneurs Festival 2025	Malang, 18 November 2025 – Civitas Akademika Jurusan Teknologi Informasi, khususnya Program Studi D4 Sistem Informasi Bisnis (SIB), dengan bangga...	<p>Mahasiswa SIB Polinema berhasil memukau juri dengan ide bisnis inovatif mereka di ajang Entrepreneurs Festival 2025.</p>	4	https://jti.polinema.ac.id/wp-content/uploads/2025/11/IMG_4055-scaled-e1763439887215-1170x600.jpg	1	t	2025-11-18 00:00:00	2025-12-03 14:55:13.704364	2025-12-03 14:55:13.704364
5	kegiatan-ai-ready-asean-untuk-siswa	Jurusan Teknologi Informasi Politeknik Negeri Malang melaksanakan kegiatan dengan tema "AI Ready ASEAN untuk Siswa"	Malang, 13 November 2025 – Jurusan Teknologi Informasi Politeknik Negeri Malang melaksanakan kegiatan "AI Ready ASEAN untuk Siswa" pada Selasa,	<p>Jurusan TI Polinema menggelar pelatihan AI untuk siswa guna mempersiapkan generasi muda menghadapi era digital di kawasan ASEAN.</p>	1	https://jti.polinema.ac.id/wp-content/uploads/2025/11/IMG_3465.HEIC-1170x600.jpg	1	t	2025-11-13 00:00:00	2025-12-03 14:55:13.704364	2025-12-03 14:55:13.704364
6	kunjungan-dari-sma-negeri-3-malang	Jurusan Teknologi Informasi Politeknik Negeri Malang menerima kunjungan dari SMA Negeri 3 Kota Malang	Malang, 12 November 2025 – Jurusan Teknologi Informasi Politeknik Negeri Malang menerima kunjungan dari SMA Negeri 3 Kota Malang dalam	<p>Kunjungan edukatif dari SMAN 3 Malang bertujuan untuk mengenalkan lingkungan kampus dan program studi yang ada di Jurusan TI Polinema.</p>	1	https://jti.polinema.ac.id/wp-content/uploads/2025/11/IMG_20251111_122621-1170x600.jpg	1	t	2025-11-12 00:00:00	2025-12-03 14:55:13.704364	2025-12-03 14:55:13.704364
7	pengumuman-persyaratan-bantuan-ukt-spp-2025	Pengumuman Persyaratan Bantuan UKT / SPP Tahun 2025	Informasi lengkap mengenai persyaratan dan tata cara pengajuan bantuan UKT/SPP tahun anggaran 2025.	<p>Bagi mahasiswa yang membutuhkan bantuan UKT, harap melengkapi berkas persyaratan sebelum tanggal yang ditentukan.</p>	2	https://jti.polinema.ac.id/wp-content/uploads/2025/11/3-768x994.png	1	t	2025-11-10 00:00:00	2025-12-03 14:55:13.704364	2025-12-03 14:55:13.704364
8	beasiswa-unggulan-kemendikbud-2025	BEASISWA UNGGULAN BAGI MASYARAKAT BERPRESTASI DAN PENYANDANG DISABILITAS 2025	Kesempatan emas bagi mahasiswa berprestasi dan penyandang disabilitas untuk mendapatkan Beasiswa Unggulan.	<p>Pendaftaran Beasiswa Unggulan Kemendikbudristek telah dibuka. Simak syarat dan ketentuannya di sini.</p>	2	https://jti.polinema.ac.id/wp-content/uploads/2025/07/beasiswa-768x360.png	1	t	2025-07-17 00:00:00	2025-12-03 14:55:13.704364	2025-12-03 14:55:13.704364
9	batas-pendaftaran-ujian-skripsi-tahap-3	Batas Pendaftaran dan Pelaksanaan Ujian Skripsi Tahap III Tahun Ajaran 2024/2025	Informasi penting bagi mahasiswa tingkat akhir mengenai deadline pendaftaran sidang skripsi Tahap III.	<p>Harap diperhatikan tanggal batas akhir upload draft skripsi. Keterlambatan tidak akan ditoleransi.</p>	2	\N	1	t	2025-07-02 00:00:00	2025-12-03 14:55:13.704364	2025-12-03 14:55:13.704364
10	diseminasi-magang-kampus-merdeka	Diseminasi Magang Kampus Merdeka Semester 6 Tahun Ajaran 2024/2025	Jadwal pelaksanaan presentasi dan diseminasi hasil kegiatan Magang Merdeka mahasiswa semester 6.	<p>Seluruh mahasiswa yang telah menyelesaikan magang wajib mengikuti kegiatan diseminasi ini sebagai syarat penilaian.</p>	3	https://jti.polinema.ac.id/diseminasi-magang-kampus-merdeka-semester-6-tahun-ajaran-2024-2025/	1	t	2025-07-07 00:00:00	2025-12-03 14:55:13.704364	2025-12-03 14:55:13.704364
\.


--
-- TOC entry 3479 (class 0 OID 18074)
-- Dependencies: 216
-- Data for Name: dosen; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.dosen (nidn, user_id, nama, jabatan, email, foto_path, researchgate_url, scholar_url, sinta_url, created_at, nip, prodi, pendidikan, sertifikasi, mata_kuliah) FROM stdin;
0308018702	3	Dr. Rakhmat Arianto, S.ST., M.Kom.	Tenaga Pengajar	arianto@sttpln.ac.id	https://jti.polinema.ac.id/wp-content/uploads/2025/07/Rakhmat-Arianto_198701082019031004.jpg	\N	https://scholar.google.com/citations?hl=en&user=MQ8PcCgAAAAJ	https://sinta.kemdiktisaintek.go.id/authors/profile/6753831	2025-12-03 14:50:51.405265	198701082019031004	Rekayasa Teknologi Informasi	S3 – Teknik Informatika, Bina Nusantara University (BINUS) (2016-2021); S2 – Teknologi Informasi, Institut Teknologi Sepuluh Nopember (2010-2013); D3 – Teknologi Informasi, Politeknik Elektronika Negeri Surabaya (2005-2009)	-	Teknologi Platform, Pembelajaran Representatif, Manajemen Korporasi, Konsultasi Publikasi Ilmiah, Etika Profesi, Data Mining, Sains Data, Pemrograman dan Pengembangan Perangkat Lunak, Pembelajaran Mesin, Metodologi Penelitian
0019038905	4	Rokhimatul Wakhidah, S.Pd., M.T.	Tenaga Pengajar	rokhimatul@polinema.ac.id	https://jti.polinema.ac.id/wp-content/uploads/2025/07/Rokhimatul-Wakhidah_198903192019032013.jpg	\N	https://scholar.google.com/citations?hl=en&user=UOVG-wcAAAAJ	https://sinta.kemdiktisaintek.go.id/authors/profile/5987801	2025-12-03 14:50:57.454181	198903192019032013	Sistem Informasi Bisnis	S2 – Teknik Informatika, Institut Teknologi Bandung (2012-2015); S1 – Pendidikan Teknik Informatika, Universitas Negeri Malang (2007-2011)	Google Sertifikat Manajemen Proyek - Coursera (Agustus 2023)	Praktikum Algoritma dan Struktur Data, Kecerdasan Artifisial, Algoritma dan Struktur Data, Workshop, \r\nPraktikum Basis Data Lanjut, Basis Data Lanjut, Manajemen Produk, Kecerdasan Bisnis, Critical Thinking dan Problem Solving
0010117109	5	Ir. Rudy Ariyanto, ST., M.Cs.	Tenaga Pengajar	ariyantorudy@gmail.com	https://jti.polinema.ac.id/wp-content/uploads/2025/07/Rudi-Ariyanto.jpg	\N	https://scholar.google.com/citations?hl=en&user=gNEBMMEAAAAJ	https://sinta.kemdiktisaintek.go.id/authors/profile/6008071	2025-12-03 14:51:09.352458	197111101999031002	Sistem Informasi Bisnis	Profesi – Insinyur, Universitas Jember (2024); S2 – Master of Computer Science, Universitas Gadjah Mada (2014); S1 – Sarjana Teknik, Universitas Brawijaya (1997)	-	Statistika, Data Mining, Praktikum Dasar Pemrograman, Perancangan Produk Kreatif, Dasar Pemrograman, Critical Thinking dan Problem Solving
0005078102	6	Ahmad Yuli Ananta, S.T., M.M.	Tenaga Pengajar	ahmadi@polinema.ac.id	https://jti.polinema.ac.id/wp-content/uploads/2025/07/ahmadi_putih.jpg	\N	https://scholar.google.com/citations?user=yvs2_GAAAAAJ&hl=id	https://sinta.kemdiktisaintek.go.id/authors/profile/6017384/	2025-12-03 14:51:15.817403	198107052005011002	Sistem Informasi Bisnis	S2 – Magister Manajemen, Universitas Gajayana (2017); S1 – Sarjana Teknik, Universitas Islam Indonesia (2004)	-	Audit Sistem Informasi, Analisis Proses Bisnis, Tata Kelola Teknologi Informasi, Manajemen Proyek
0017129402	7	Candra Bella Vista, S.Kom., MT.	Tenaga Pengajar	bellavista@polinema.ac.id	https://jti.polinema.ac.id/wp-content/uploads/2025/07/Candra-Bella-Vista.jpg	\N	https://scholar.google.com/citations?user=1DEg4z8AAAAJ&hl=id	https://sinta.kemdiktisaintek.go.id/authors/profile/6777490	2025-12-03 14:51:21.237997	199412172019032020	Teknik Informatika	S2 – Teknik Informatika, Institut Teknologi Bandung (2015-2017); S1 – Teknik Informatika, Universitas Brawijaya (2011-2015)	-	Praktikum Basis Data, Basis Data, Rekayasa Perangkat Lunak, Kecerdasan Bisnis
0031019404	8	Endah Septa Sintiya, S.Pd., M.Kom	Tenaga Pengajar	e.septa@polinema.ac.id	https://jti.polinema.ac.id/wp-content/uploads/2025/07/Endah-Septa-Sintiya.jpg	\N	https://scholar.google.com/citations?user=rTFTuCYAAAAJ&hl=en	https://sinta.kemdiktisaintek.go.id/authors/profile/6813467	2025-12-03 14:51:26.272592	\N	Pengembangan Perangkat (Piranti) Lunak Situs	S2 – Sistem Informasi, Institut Teknologi Sepuluh Nopember (2017-2019); S1 – Pendidikan Teknik Informatika, Universitas Negeri Malang (2012-2016)	Google Project Management Certificate - Coursera (Sept 2023); IT Specialist - HTML & CSS - Certiport (Agustus 2022); Google Analytics Certification - Google Digital Academy (Okt 2024)	Proyek Sistem Informasi, Pemrograman Web Lanjut, Data Warehouse, Praktikum Pemrograman Frontend, Praktikum Pemrograman Berbasis Objek, Pemrograman Frontend, Pemrograman Berbasis Objek
0009118305	9	Dhebys Suryani, S.Kom., MT	Tenaga Pengajar	dhebys.suryani@gmail.com	https://jti.polinema.ac.id/wp-content/uploads/2025/07/Dhebys-Suryani.jpg	\N	https://scholar.google.com/citations?user=k46osOYAAAAJ&hl=id	https://sinta.kemdiktisaintek.go.id/authors/profile/6018887	2025-12-03 14:51:31.870336	198311092014042001	Manajemen Informatika (Kampus Kab.Pamekasan)	S2 – Teknologi Informasi, Universitas Brawijaya (2011-2013); S1 – Teknologi Informasi, Universitas Trunojoyo Madura (2002-2007)	-	Pengenalan Sistem Informasi, Pemasaran Digital, Manajemen Hubungan Pelanggan, Komputasi Multimedia, Sistem Informasi Manajemen, Konsep Teknologi Informasi, Matematika Diskrit, Kewirausahaan Berbasis Teknologi
0007108905	10	Farid Angga Pribadi, S.Kom., M.Kom	Tenaga Pengajar	faridangga@polinema.ac.id	https://jti.polinema.ac.id/wp-content/uploads/2025/07/Farid-Angga-Pribadi_198910072020121003.jpg	\N	https://scholar.google.com/citations?hl=en&user=l2ZPw6YAAAAJ	https://sinta.kemdiktisaintek.go.id/authors/profile/6799316	2025-12-03 14:51:37.751538	\N	Sistem Informasi Bisnis	S2 – Sistem Informasi, Institut Teknologi Sepuluh Nopember Surabaya (2015-2018); S1 – Teknologi Informasi, Universitas Brawijaya (2007-2013)	Microsoft Certified: Azure AI Fundamentals - Microsoft (Okt 2022); Cobit 5 Foundation Certificate - PeopleCert (Jan 2022); Web Developer - BNSP (Okt 2021)	Proyek Sistem Informasi, Praktikum Basis Data, Basis Data, Manajemen Hubungan Pelanggan, Workshop, Tata Kelola Teknologi Informasi, Sistem Informasi Manajemen, Kewirausahaan Berbasis Teknologi
0021058301	11	Hendra Pradibta, SE., M.Sc.	Tenaga Pengajar	hendra.pradibta@polinema.ac.id	https://jti.polinema.ac.id/wp-content/uploads/2025/07/Hendra-Pradibta_198305212006041003.jpg	\N	https://scholar.google.com/citations?user=XeFZX9kAAAAJ&hl=en	https://sinta.kemdiktisaintek.go.id/authors/profile/5996924	2025-12-03 14:51:44.048932	198305212006041003	Sistem Informasi Bisnis	S2 – E-Business and Information Systems, Newcastle University (2009-2010); S1 – Sarjana Ekonomi, Universitas Brawijaya (2001)	-	Pengembangan Karir, Analisis Proses Bisnis, Pengantar Akuntansi, Manajemen, dan Bisnis, Manajemen Produk, Kewirausahaan Berbasis Teknologi
1234567890	21	Lovie Jechonia T S.Tr	Asisten Ahli	dosenlovie@gmail.com	\N	\N	\N	\N	2025-12-12 19:16:10.898898	\N	Teknik Informatika	\N	\N	\N
\.


--
-- TOC entry 3500 (class 0 OID 18769)
-- Dependencies: 237
-- Data for Name: dosen_focus; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.dosen_focus (nidn, focus_id) FROM stdin;
\.


--
-- TOC entry 3482 (class 0 OID 18122)
-- Dependencies: 219
-- Data for Name: galeri; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.galeri (galeri_id, judul, deskripsi, file_path, tanggal, uploaded_by, created_at) FROM stdin;
2	Sudut Pintu Masuk & Lemari	Area pintu masuk kelas yang dilengkapi dengan lemari penyimpanan inventaris dan papan tulis putih.	https://jti.polinema.ac.id/wp-content/uploads/2025/08/IMG_2757-scaled.jpg	2023-10-25	1	2025-12-03 14:53:37.399323
3	Fasilitas Presentasi Utama	Ruang kelas dilengkapi layar proyektor gantung dan papan tulis lebar untuk mendukung kegiatan belajar mengajar.	https://jti.polinema.ac.id/wp-content/uploads/2025/08/IMG_2925-scaled.jpg	2023-10-25	1	2025-12-03 14:53:37.399323
4	Pencahayaan & Sirkulasi Udara	Sisi ruang kelas dengan jendela besar dan tirai biru, memberikan pencahayaan alami yang cukup.	https://jti.polinema.ac.id/wp-content/uploads/2025/08/IMG_2756-scaled.jpg	2023-10-25	1	2025-12-03 14:53:37.399323
5	Tata Letak Meja Diskusi	Susunan meja dosen dan mahasiswa yang rapi, dilengkapi fasilitas stopkontak di setiap meja untuk penggunaan laptop.	https://jti.polinema.ac.id/wp-content/uploads/2025/08/IMG_2924-scaled.jpg	2023-10-25	1	2025-12-03 14:53:37.399323
\.


--
-- TOC entry 3497 (class 0 OID 18673)
-- Dependencies: 234
-- Data for Name: kategori_publikasi; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.kategori_publikasi (id, nama_kategori) FROM stdin;
1	Sinta
2	Scopus
3	Scholar
\.


--
-- TOC entry 3480 (class 0 OID 18089)
-- Dependencies: 217
-- Data for Name: mahasiswa; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.mahasiswa (nim, user_id, nama, prodi, tingkat, no_hp, email, created_at, initial_password) FROM stdin;
244107060101	17	Lovie Jechonia Tonimba	D4 SIB	2	082271413842	lovie@example.com	2025-12-10 13:34:22.798071	\N
112233445566	22	lovieee	D-IV Sistem Informasi Bisnis	3	082271413842	example@gmail.com	2025-12-12 21:22:48.599628	\N
244107020163	23	Ka Abi Muhammad R F	D-IV Teknik Informatika	1	085707050123	abi@gmail.com	2025-12-12 22:18:34.926607	\N
24410702000	27	Mery	Teknik Informatika	1	\N	meeryy@gmail.com	2025-12-15 14:15:52.462048	\N
2441070618	25	Mery	D-IV Sistem Informasi Bisnis	1	08123456789	mery@gmail.com	2025-12-15 14:06:26.396885	\N
2511111111	39	Milo Tonimba	D-IV Sistem Informasi Bisnis	3	08000000	milo2@gmail.com	2025-12-16 15:33:15.280397	vDmYW4C5
13357973	40	qqq	D-II PPLS	4		qqq@gmail.com	2025-12-16 15:36:23.014194	wMDSpraF
25098765432	30	Milo T	D-IV Teknik Informatika	1	12344567	milo@gmail.com	2025-12-16 13:39:42.35247	\N
250311619109	41	Ka Abi Muhammad Robit F.	D-IV Teknik Informatika	2	085707045614	kaabi@gmail.com	2025-12-16 20:56:08.521594	\N
123456789	42	meri	S2 Magister Terapan	S2	\N	meri@gmail.com	2025-12-18 20:19:55.084624	\N
25410706123	28	Giftly Netanael Tonimba	D-IV Teknik Informatika	1	08124356879	giftly@gmail.com	2025-12-15 16:45:39.486552	\N
987654321	45	sky	Teknik Informatika	1	\N	sky@gmail.com	2025-12-26 22:10:14.608525	\N
\.


--
-- TOC entry 3499 (class 0 OID 18680)
-- Dependencies: 236
-- Data for Name: publikasi; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.publikasi (id, judul, external_link, kategori_id, dosen_nidn, status, created_at, updated_at, focus_id, tahun) FROM stdin;
2	Implementation IoT in system monitoring hydroponic plant water circulation and control	https://scholar.google.com/citations?view_op=view_citation&hl=en&user=XeFZX9kAAAAJ&pagesize=100&sortby=pubdate&citation_for_view=XeFZX9kAAAAJ:IWHjjKOFINEC	2	1234567890	pending	2025-12-17 16:50:56.31532	2025-12-17 16:50:56.31532	2	2020
3	publikasi punya lovie	https://scholar.google.com/citations?view_op=view_citation&hl=en&user=XeFZX9kAAAAJ&pagesize=100&sortby=pubdate&citation_for_view=XeFZX9kAAAAJ:IWHjjKOFINEC	1	1234567890	pending	2025-12-17 19:46:58.585238	2025-12-17 19:46:58.585238	3	2024
1	Implementation IoT in system monitoring hydroponic plant water circulation and control	https://scholar.google.com/citations?view_op=view_citation&hl=en&user=XeFZX9kAAAAJ&pagesize=100&sortby=pubdate&citation_for_view=XeFZX9kAAAAJ:IWHjjKOFINEC	3	0021058301	published	2025-12-15 17:37:46.308097	2025-12-15 17:37:46.308097	2	2018
\.


--
-- TOC entry 3490 (class 0 OID 18248)
-- Dependencies: 227
-- Data for Name: research_focus; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.research_focus (focus_id, nama_fokus, description) FROM stdin;
1	Artificial Intelligence	\N
2	Internet of Things	\N
3	Data Science	\N
4	Cyber Security	\N
\.


--
-- TOC entry 3484 (class 0 OID 18138)
-- Dependencies: 221
-- Data for Name: sarana; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.sarana (sarana_id, nama_sarana, deskripsi, tipe, status, foto_path, created_at) FROM stdin;
1	Laboratorium Business Analyst	Laboratorium yang dikhususkan untuk praktikum analisis bisnis, perancangan sistem, dan pengolahan data. Dilengkapi dengan perangkat komputer spesifikasi tinggi dan software analisis (seperti Tableau, PowerBI, SPSS).	Laboratorium	Tersedia	https://jti.polinema.ac.id/wp-content/uploads/2025/08/IMG_2925-scaled.jpg	2025-12-03 14:51:47.836424
\.


--
-- TOC entry 3478 (class 0 OID 18058)
-- Dependencies: 215
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.users (user_id, username, password_hash, role, email, display_name, created_at) FROM stdin;
1	admin1	$2y$10$N2fFwnQYyYV4c0ZC8Yl5uO3PYoFNjFdcyE4XDdnv4YF9kczZHEjEO	admin	\N	Admin 1	2025-11-28 05:56:51.04214
2	admin2	$2y$10$vknqqkQvazkz2d5HK8QyQuPMBzGwos3pkNMwo/XKu90I00SdR9GgS	admin	\N	Admin 2	2025-11-28 05:56:51.04214
3	RakhmatArianto	$2y$10$9KgL7tzhLwhKQ0q5su5FuOZThZyFAk9MSZsulNgrsD.4IY9hRDkIG	dosen	\N	Rakhmat Arianto	2025-11-28 05:56:51.04214
4	RokhimatulWakhidah	$2y$10$PtlmIoU3x2W1ngDNDG0UCOU2HySvDrJ4Y9EcRZ0Z7wZJwXbJZc4DC	dosen	\N	Rokhimatul Wakhidah	2025-11-28 05:56:51.04214
5	RudyAriyanto	$2y$10$9pP2Z/TvAMoAmdu79xjZmeThp0mxW6pPQTUdGeqEkU8GLzdbEBYne	dosen	\N	Rudy Ariyanto	2025-11-28 05:56:51.04214
6	AhmadiYuliAnanta	$2y$10$K5laTlQy3nWhcw9OHXoQve8v9iMNqMEl0/amF91WCLP5LlXQRqLui	dosen	\N	Ahmadi Yuli Ananta	2025-11-28 05:56:51.04214
7	CandraBellaVista	$2y$10$3K7Vy/nSS8yRVpR5buwu8uhhl4KkqwVWAC5ZLAwpHnQqoNe0NkZki	dosen	\N	Candra Bella Vista	2025-11-28 05:56:51.04214
8	EndahSeptaSintiya	$2y$10$7eVhivpYx4WzsMWPNZ3l6uHw7VScik5wE0JVHPAYn2LyH6eJkRUs6	dosen	\N	Endah Septa Sintiya	2025-11-28 05:56:51.04214
9	DhebysSuryani	$2y$10$pHBDQecBoJlaiaVC6/REduYvZkMAUjTuHomDA7HXc2cPLL/n2S2wK	dosen	\N	Dhebys Suryani	2025-11-28 05:56:51.04214
10	FaridAnggaPribadi	$2y$10$V3AMN98uJ8YiEpBUwjMuGUSHhYp82IhZrwTdYycttWMcSsHzy.bX6	dosen	\N	Farid Angga Pribadi	2025-11-28 05:56:51.04214
11	HendraPradibta	$2y$10$sdyllYwGwvJjv29PJ8oLzOCcprtSe9syk1B6DVmZ6bEz7UWQiE3RK	dosen	\N	Hendra Pradibta	2025-11-28 05:56:51.04214
12	lovie	$2y$10$E7dzMbKFWxCaQNr26RQY8eQFFqradx7tLVzlhjm6T4iiBLvfOuWfq	mahasiswa	jechonialovie@gmail.com	Lovie Jechonia Tonimba	2025-11-28 05:56:51.04214
13	loviee	$2y$10$K87m2j2pTePbp6VGXWWDke5bb8wXpfkomFYM67bo2LwQswr1bBkXa	mahasiswa	lovietonimba@gmail.com	Lovie Tonimba	2025-12-05 07:58:55.505016
14	lovi	$2y$10$reZQWzacMkNyQdR1L1s6zu7A6JkGAKjmGS43Fi9YIMGzpAnu5bzru	admin	admin.lovi@example.com	Admin Lovie	2025-12-10 10:40:43.903932
17	244107060101	$2y$10$3ltq/c0IULmIDfmQb6ERkOB/366gAzJFpx4ImuoRWrRluHFghdAOq	mahasiswa	lovie@example.com	Lovie Jechonia Tonimba	2025-12-10 13:34:22.798071
19	123456789	$2y$10$zcC9CnUd/4xd.mk6ooRN2eFFnT6orWVa0VoetTYMcsqGO2.DfzDLW	mahasiswa	lovie@gmail.com	Lovely	2025-12-11 15:58:26.501945
20	Lovie Jechonia T	$2y$10$TlI1VK.kSJHrvtttmKile.mXRsGRBOUwn9xKknrqq6eArslEte7CS	mahasiswa	loviee@gmail.com	Lovie Jechonia T	2025-12-11 16:06:27.814492
21	Lovie Jechonia T S.Tr	$2y$10$M4pdvinmEWCS/NoljdwLi.gmjt7pIc3yUilW/4Dg2oFbPkziuYM2q	dosen	dosenlovie@gmail.com	Lovie Jechonia T S.Tr	2025-12-12 19:16:10.898898
22	lov	$2y$10$0rNLIwS7YfhJlEODLwx/bePOL6iq2FhXcGwNQY21I1GWMAdZAU.vu	mahasiswa	example@gmail.com	lovie	2025-12-12 21:22:48.599628
23	abi	$2y$10$28q33v5C5NLJAnGxNAX8EeSZRsxAiOsL8kk3AVDWns0m85DepJQaa	mahasiswa	abi@gmail.com	Ka Abi M R F	2025-12-12 22:18:34.926607
24	244107020111	$2y$10$MD0B4OTUpasuASX6kUQ0D.YgNM70qwjMiQMnnODf6XGl3kKniaJsi	mahasiswa	abiabi@gmail.com	ka abi	2025-12-13 12:10:45.878707
25	2441070618	$2y$10$JknnUMk3bf6/VA3UUf5sIOj7LIgCHni87Tu4tkpkwu0QH1BWbz/R2	mahasiswa	mery@gmail.com	Mery	2025-12-15 14:06:26.396885
27	mery	$2y$10$tyLE4aXwsUn1I3tP5xfsmeeyDk1d6CWdu7uD5B7iMYgZNXKoYpCIS	mahasiswa	meeryy@gmail.com	Mery	2025-12-15 14:15:52.462048
28	gift	$2y$10$XItVdsVYJOwi5rRl.otIbO7xlw9y9QZ8R5rVz8djbaACCZZaXR3f2	mahasiswa	gift@gmail.com	Giftly Netanael T	2025-12-15 16:45:39.486552
30	milo	$2y$10$YtulnOQh501smV6wFm4PWuyJJqYq2QePWQ66HpOfv..UwBwahk44S	mahasiswa	milo@gmail.com	Milo T	2025-12-16 13:39:42.35247
39	2511111111	$2y$10$4FY6AAQi6EPBzHWhlKs9IOCc7dU4ObahAKiOrv47PWU/6otajCtdC	mahasiswa	milo2@gmail.com	milo2	2025-12-16 15:33:15.280397
40	13357973	$2y$10$SL7V5DGYBtZHw2l/nv1GXOtASpOJwQVs8gP83VLOM/QFupNhBTWJi	mahasiswa	qqq@gmail.com	qqq	2025-12-16 15:36:23.014194
41	kaabiii	$2y$10$E/TWwvkBhK3kDkVx/qEo4uOQ7BHOrFox//bgl.9NFN27PW2ptQUsm	mahasiswa	kaabi@gmail.com	Ka Abi Muhammad Robit F.	2025-12-16 20:56:08.521594
42	meri	$2y$10$xn.tl8yWgG9uJDLDkohS8unBP3lC4p/aBOOUky6LDbT8MPllUQoNu	mahasiswa	meri@gmail.com	meri	2025-12-18 20:19:55.084624
44	lovii	$2y$10$6cY6dG4axn2m24ZeLC560uxm9UkUuKDbkFBHotDtyPOvcvWCYzrL6	admin	admin.lovii@example.com	Admin Lovi	2025-12-19 13:49:55.687843
45	sky	$2y$10$oO5euIjz.AYSTwoJ1MDqWulgbzTWp5ajcBpjcmJJtZTDckTGODTCW	mahasiswa	sky@gmail.com	sky	2025-12-26 22:10:14.608525
\.


--
-- TOC entry 3517 (class 0 OID 0)
-- Dependencies: 230
-- Name: blocked_dates_block_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.blocked_dates_block_id_seq', 1, false);


--
-- TOC entry 3518 (class 0 OID 0)
-- Dependencies: 228
-- Name: bookings_booking_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.bookings_booking_id_seq', 31, true);


--
-- TOC entry 3519 (class 0 OID 0)
-- Dependencies: 222
-- Name: content_categories_category_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.content_categories_category_id_seq', 4, true);


--
-- TOC entry 3520 (class 0 OID 0)
-- Dependencies: 224
-- Name: contents_content_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.contents_content_id_seq', 10, true);


--
-- TOC entry 3521 (class 0 OID 0)
-- Dependencies: 218
-- Name: galeri_galeri_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.galeri_galeri_id_seq', 5, true);


--
-- TOC entry 3522 (class 0 OID 0)
-- Dependencies: 233
-- Name: kategori_publikasi_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.kategori_publikasi_id_seq', 1, false);


--
-- TOC entry 3523 (class 0 OID 0)
-- Dependencies: 235
-- Name: publikasi_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.publikasi_id_seq', 3, true);


--
-- TOC entry 3524 (class 0 OID 0)
-- Dependencies: 226
-- Name: research_focus_focus_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.research_focus_focus_id_seq', 1, false);


--
-- TOC entry 3525 (class 0 OID 0)
-- Dependencies: 220
-- Name: sarana_sarana_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.sarana_sarana_id_seq', 1, true);


--
-- TOC entry 3526 (class 0 OID 0)
-- Dependencies: 214
-- Name: users_user_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.users_user_id_seq', 45, true);


--
-- TOC entry 3309 (class 2606 OID 18456)
-- Name: blocked_dates blocked_dates_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.blocked_dates
    ADD CONSTRAINT blocked_dates_pkey PRIMARY KEY (block_id);


--
-- TOC entry 3304 (class 2606 OID 18312)
-- Name: bookings bookings_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bookings
    ADD CONSTRAINT bookings_pkey PRIMARY KEY (booking_id);


--
-- TOC entry 3292 (class 2606 OID 18158)
-- Name: content_categories content_categories_nama_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.content_categories
    ADD CONSTRAINT content_categories_nama_key UNIQUE (nama);


--
-- TOC entry 3294 (class 2606 OID 18156)
-- Name: content_categories content_categories_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.content_categories
    ADD CONSTRAINT content_categories_pkey PRIMARY KEY (category_id);


--
-- TOC entry 3296 (class 2606 OID 18170)
-- Name: contents contents_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.contents
    ADD CONSTRAINT contents_pkey PRIMARY KEY (content_id);


--
-- TOC entry 3298 (class 2606 OID 18172)
-- Name: contents contents_slug_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.contents
    ADD CONSTRAINT contents_slug_key UNIQUE (slug);


--
-- TOC entry 3278 (class 2606 OID 18083)
-- Name: dosen dosen_email_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dosen
    ADD CONSTRAINT dosen_email_key UNIQUE (email);


--
-- TOC entry 3315 (class 2606 OID 18773)
-- Name: dosen_focus dosen_focus_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dosen_focus
    ADD CONSTRAINT dosen_focus_pkey PRIMARY KEY (nidn, focus_id);


--
-- TOC entry 3280 (class 2606 OID 18357)
-- Name: dosen dosen_nip_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dosen
    ADD CONSTRAINT dosen_nip_key UNIQUE (nip);


--
-- TOC entry 3282 (class 2606 OID 18081)
-- Name: dosen dosen_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dosen
    ADD CONSTRAINT dosen_pkey PRIMARY KEY (nidn);


--
-- TOC entry 3288 (class 2606 OID 18131)
-- Name: galeri galeri_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.galeri
    ADD CONSTRAINT galeri_pkey PRIMARY KEY (galeri_id);


--
-- TOC entry 3311 (class 2606 OID 18678)
-- Name: kategori_publikasi kategori_publikasi_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.kategori_publikasi
    ADD CONSTRAINT kategori_publikasi_pkey PRIMARY KEY (id);


--
-- TOC entry 3284 (class 2606 OID 18098)
-- Name: mahasiswa mahasiswa_email_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.mahasiswa
    ADD CONSTRAINT mahasiswa_email_key UNIQUE (email);


--
-- TOC entry 3286 (class 2606 OID 18096)
-- Name: mahasiswa mahasiswa_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.mahasiswa
    ADD CONSTRAINT mahasiswa_pkey PRIMARY KEY (nim);


--
-- TOC entry 3313 (class 2606 OID 18690)
-- Name: publikasi publikasi_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.publikasi
    ADD CONSTRAINT publikasi_pkey PRIMARY KEY (id);


--
-- TOC entry 3300 (class 2606 OID 18257)
-- Name: research_focus research_focus_nama_fokus_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.research_focus
    ADD CONSTRAINT research_focus_nama_fokus_key UNIQUE (nama_fokus);


--
-- TOC entry 3302 (class 2606 OID 18255)
-- Name: research_focus research_focus_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.research_focus
    ADD CONSTRAINT research_focus_pkey PRIMARY KEY (focus_id);


--
-- TOC entry 3290 (class 2606 OID 18147)
-- Name: sarana sarana_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sarana
    ADD CONSTRAINT sarana_pkey PRIMARY KEY (sarana_id);


--
-- TOC entry 3272 (class 2606 OID 18071)
-- Name: users users_email_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_key UNIQUE (email);


--
-- TOC entry 3274 (class 2606 OID 18067)
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (user_id);


--
-- TOC entry 3276 (class 2606 OID 18069)
-- Name: users users_username_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_username_key UNIQUE (username);


--
-- TOC entry 3305 (class 1259 OID 18340)
-- Name: idx_bookings_dosen; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_bookings_dosen ON public.bookings USING btree (booking_dosen_nidn);


--
-- TOC entry 3306 (class 1259 OID 18339)
-- Name: idx_bookings_mahasiswa; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_bookings_mahasiswa ON public.bookings USING btree (mahasiswa_nim);


--
-- TOC entry 3307 (class 1259 OID 18338)
-- Name: idx_bookings_tanggal_sarana_slot; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_bookings_tanggal_sarana_slot ON public.bookings USING btree (tanggal, sarana_id, start_time);


--
-- TOC entry 3333 (class 2620 OID 18183)
-- Name: contents trg_contents_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_contents_updated_at BEFORE UPDATE ON public.contents FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- TOC entry 3332 (class 2620 OID 18073)
-- Name: users trg_users_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trg_users_updated_at BEFORE UPDATE ON public.users FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();


--
-- TOC entry 3326 (class 2606 OID 18462)
-- Name: blocked_dates blocked_dates_created_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.blocked_dates
    ADD CONSTRAINT blocked_dates_created_by_fkey FOREIGN KEY (created_by) REFERENCES public.users(user_id);


--
-- TOC entry 3327 (class 2606 OID 18457)
-- Name: blocked_dates blocked_dates_sarana_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.blocked_dates
    ADD CONSTRAINT blocked_dates_sarana_id_fkey FOREIGN KEY (sarana_id) REFERENCES public.sarana(sarana_id) ON DELETE CASCADE;


--
-- TOC entry 3321 (class 2606 OID 18318)
-- Name: bookings bookings_booking_dosen_nidn_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bookings
    ADD CONSTRAINT bookings_booking_dosen_nidn_fkey FOREIGN KEY (booking_dosen_nidn) REFERENCES public.dosen(nidn);


--
-- TOC entry 3322 (class 2606 OID 18328)
-- Name: bookings bookings_created_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bookings
    ADD CONSTRAINT bookings_created_by_fkey FOREIGN KEY (created_by) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- TOC entry 3323 (class 2606 OID 18333)
-- Name: bookings bookings_handled_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bookings
    ADD CONSTRAINT bookings_handled_by_fkey FOREIGN KEY (handled_by) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- TOC entry 3324 (class 2606 OID 18313)
-- Name: bookings bookings_mahasiswa_nim_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bookings
    ADD CONSTRAINT bookings_mahasiswa_nim_fkey FOREIGN KEY (mahasiswa_nim) REFERENCES public.mahasiswa(nim) ON DELETE SET NULL;


--
-- TOC entry 3325 (class 2606 OID 18323)
-- Name: bookings bookings_sarana_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.bookings
    ADD CONSTRAINT bookings_sarana_id_fkey FOREIGN KEY (sarana_id) REFERENCES public.sarana(sarana_id);


--
-- TOC entry 3319 (class 2606 OID 18178)
-- Name: contents contents_admin_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.contents
    ADD CONSTRAINT contents_admin_id_fkey FOREIGN KEY (admin_id) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- TOC entry 3320 (class 2606 OID 18173)
-- Name: contents contents_category_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.contents
    ADD CONSTRAINT contents_category_id_fkey FOREIGN KEY (category_id) REFERENCES public.content_categories(category_id) ON DELETE SET NULL;


--
-- TOC entry 3330 (class 2606 OID 18779)
-- Name: dosen_focus dosen_focus_focus_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dosen_focus
    ADD CONSTRAINT dosen_focus_focus_id_fkey FOREIGN KEY (focus_id) REFERENCES public.research_focus(focus_id) ON DELETE CASCADE;


--
-- TOC entry 3331 (class 2606 OID 18774)
-- Name: dosen_focus dosen_focus_nidn_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dosen_focus
    ADD CONSTRAINT dosen_focus_nidn_fkey FOREIGN KEY (nidn) REFERENCES public.dosen(nidn) ON DELETE CASCADE;


--
-- TOC entry 3316 (class 2606 OID 18084)
-- Name: dosen dosen_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.dosen
    ADD CONSTRAINT dosen_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- TOC entry 3328 (class 2606 OID 18696)
-- Name: publikasi fk_publikasi_dosen; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.publikasi
    ADD CONSTRAINT fk_publikasi_dosen FOREIGN KEY (dosen_nidn) REFERENCES public.dosen(nidn) ON DELETE CASCADE;


--
-- TOC entry 3329 (class 2606 OID 18691)
-- Name: publikasi fk_publikasi_kategori; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.publikasi
    ADD CONSTRAINT fk_publikasi_kategori FOREIGN KEY (kategori_id) REFERENCES public.kategori_publikasi(id) ON DELETE SET NULL;


--
-- TOC entry 3318 (class 2606 OID 18132)
-- Name: galeri galeri_uploaded_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.galeri
    ADD CONSTRAINT galeri_uploaded_by_fkey FOREIGN KEY (uploaded_by) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- TOC entry 3317 (class 2606 OID 18099)
-- Name: mahasiswa mahasiswa_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.mahasiswa
    ADD CONSTRAINT mahasiswa_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- TOC entry 3495 (class 0 OID 18621)
-- Dependencies: 232 3502
-- Name: vw_peminjaman_history; Type: MATERIALIZED VIEW DATA; Schema: public; Owner: postgres
--

REFRESH MATERIALIZED VIEW public.vw_peminjaman_history;


-- Completed on 2025-12-30 22:44:22

--
-- PostgreSQL database dump complete
--

\unrestrict vH3V8lOY4Y5zPPUgXqj8OGHgQdoOoNy9SadSTtyueHocgGq2BHCtNHMyUFqZ9PY

-- Completed on 2025-12-30 22:44:22

--
-- PostgreSQL database cluster dump complete
--

